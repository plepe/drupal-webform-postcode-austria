<?php

namespace Drupal\webform_postcode_austria;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use PhpOffice\PhpSpreadsheet\Reader;

/**
 * Perform postcode lookup.
 */
class PostcodeLookup {
  protected $data;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * Constructs a new PostcodeLookup.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   */
  public function __construct(CacheBackendInterface $cache_backend) {
    $this->cacheBackend = $cache_backend;
  }

  public function importData () {
    $page = file_get_contents('https://www.post.at/g/c/postlexikon');
    if (!preg_match('/"PLZ Verzeichnis" href="(.*)" download/', $page, $m)) {
      watchdog_exception('webform_postcode_austria', new \Exception('Can\'t parse URL of PLZ XLSX from Postlexikon: "' . error_get_last()['message'] . '"'));
      return;
    }
    $xlsxUrl = $m[1];

    $contents = file_get_contents($xlsxUrl);
    if ($contents === false) {
      watchdog_exception('webform_postcode_austria', new \Exception('Error downloading PLZ XLSX: "' . error_get_last()['message'] . '"'));
      return;
    }
    file_put_contents('/tmp/postcode_austria.xlsx', $contents);

    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load('/tmp/postcode_austria.xlsx');

    $worksheet = $spreadsheet->getActiveSheet();
    $contents = $worksheet->toArray();

    $columns = array_shift($contents);
    $data = [];
    foreach ($contents as $row) {
      $item = [];
      foreach ($columns as $i => $col) {
        $item[$col] = $row[$i];
      }

      $data[$item['PLZ']] = $this->convert($item);
    }

    $this->cacheBackend->set('webform_postcode_austria:data', $data, strtotime('+24 hour'));
  }

  public function loadData () {
    $cache = $this->cacheBackend->get('webform_postcode_austria:data');
    if ($cache) {
      $this->data = $cache->data;
      return;
    }

    $this->importData();

    $this->data = $data;
  }

  public function getPostcode(string $postcode) {
    if (!$this->data) {
      $this->loadData();
    }

    if (!$this->data) {
      return null;
    }

    if (!array_key_exists($postcode, $this->data)) {
      return null;
    }

    return $this->data[$postcode];
  }

  public function convert ($item) {
    $bundeslandMapping = [
      'W' => 'Wien',
      'N' => 'Niederösterreich',
      'S' => 'Salzburg',
      'V' => 'Vorarlberg',
      'St' => 'Steiermark',
      'K' => 'Kärnten',
      'B' => 'Burgenland',
      'O' => 'Oberösterreich',
      'T' => 'Tirol',
    ];

    $result = [
      'plz' => $item['PLZ'],
      'ort' => $item['Ort'],
      'bundesland' => $bundeslandMapping[$item['Bundesland']],
    ];

    return $result;
  }

}

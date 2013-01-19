<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 2:02
 */
define('USE_DEBUG', 1);

include(dirname(__FILE__).'/init.php');

$cl = new SphinxClient();
$cl->SetServer( 'localhost', 9313 );

// Собственно поиск
$cl->SetMatchMode( SPH_MATCH_EXTENDED  ); // ищем хотя бы 1 слово из поисковой фразы
$cl->SetLimits(0, 120, 120, 0);

$dateStart = time() - 30*24*60*60;
if (isset($_POST['date_start'])) {
    $dateStartParsed = DateTime::createFromFormat('d.m.Y', $_POST['date_start']);
    if ($dateStartParsed) {
        $dateStart = $dateStartParsed->getTimestamp();
    }
}
$dateEnd = time();
if (isset($_POST['date_end'])) {
    $dateEndParsed = DateTime::createFromFormat('d.m.Y', $_POST['date_end']);
    if ($dateEndParsed) {
        $dateEnd = $dateEndParsed->getTimestamp();
    }
}

$cl->SetFilterRange('date', $dateStart, $dateEnd);

$registry = CRegistryJSON::getInstance();
$phraseGroups = $registry->get('phrases');

$queries = array();
foreach ($phraseGroups as $phraseGroup) {
    $queryPhrases = array();
    foreach ($phraseGroup['phrases'] as $phrase) {
        $queryPhrases[] = '"'.$phrase.'"';
    }
    $queries[] = implode(' | ', $queryPhrases);
}

foreach($queries as $query) {
    $cl->AddQuery($query);
}

$dbResult = $cl->RunQueries();

// обработка результатов запроса
if ( $dbResult === false ) {
    die('{"error:", "'.$cl->getLastError().'"}'); // выводим ошибку если произошла
} else {
    if ( $cl->GetLastWarning() ) {
        echo "WARNING: " . $cl->GetLastWarning();
    }

    $results = array();
    foreach($dbResult as $index => $res) {

        if ( ! empty($res["matches"]) ) {
            $ids = array_keys($res['matches']);

            $dbDocuments = DBQuery::withTable('news')
                ->getFields(array('date', 'source_url'))
                ->where(array('_id' => $ids))
                ->order('date')
                ->fetchAll();

            $results[] = array(
                'query'         => $phraseGroups[$index]['name'],
                'documents'     => $dbDocuments,
                'total_found'   => $res['total_found']
            );
        }
    }

    echo json_encode($results);
}

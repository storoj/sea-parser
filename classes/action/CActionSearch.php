<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 23.01.13
 * Time: 0:54
 */

define('SEARCH_VERBOSE_NONE', 0);
define('SEARCH_VERBOSE_DATE', 1);
define('SEARCH_VERBOSE_FULL', 2);

class CActionSearch extends CAction
{
    public function action_index()
    {
        $cl = new SphinxClient();
        $cl->SetServer( 'localhost', 9313 );

        // Собственно поиск
        $cl->SetMatchMode(SPH_MATCH_EXTENDED);
        $cl->SetLimits(0, 120, 120, 0);

        $dateStart = time() - 30*24*60*60;

        if (isset($this->postData['date_start'])) {
            $dateStartParsed = DateTime::createFromFormat('d.m.Y', $this->postData['date_start']);
            if ($dateStartParsed) {
                $dateStart = $dateStartParsed->getTimestamp();
            }
        }
        $dateEnd = time();
        if (isset($this->postData['date_end'])) {
            $dateEndParsed = DateTime::createFromFormat('d.m.Y', $this->postData['date_end']);
            if ($dateEndParsed) {
                $dateEnd = $dateEndParsed->getTimestamp();
            }
        }

        $cl->SetFilterRange('date', $dateStart, $dateEnd);

        $registry = CRegistryJSON::getInstance();
        $phrasesGroups = $registry->get('phrases');

        if (isset($this->postData['phrasesGroups'])) {
            $initialGroups = $phrasesGroups;
            $phrasesGroups = array();

            foreach($this->postData['phrasesGroups'] as $groupID) {
                if (isset($initialGroups[$groupID])) {
                    $phrasesGroups[] = $initialGroups[$groupID];
                }
            }
        }

        if (isset($this->postData['source_id'])) {
            $cl->SetFilter('source_id', $this->postData['source_id']);
        }

        $searchIndex = isset($this->postData['search_type']) && $this->postData['search_type'] == 'exact'
            ? 'newsIndexExact'
            : 'newsIndex';

        $verbose = isset($this->postData['verbose'])
            ? $this->postData['verbose']
            : SEARCH_VERBOSE_NONE;

        $queries = array();
        foreach ($phrasesGroups as $phraseGroup) {
            $queryPhrases = array();
            foreach ($phraseGroup['phrases'] as $phrase) {
                $queryPhrases[] = '"'.$phrase.'"';
            }
            $queries[] = implode(' | ', $queryPhrases);
        }

        foreach($queries as $query) {
            $cl->AddQuery($query, $searchIndex);
        }

        $dbResult = $cl->RunQueries();

        // обработка результатов запроса
        if ($dbResult === false) {
            $this->setStatus('error', $cl->getLastError());
            return false;
        }

        $lastWarning = $cl->GetLastWarning();
        if ($lastWarning) {
            $this->setStatus('error', $lastWarning);
            return false;
        }

        $results = array();
        foreach($dbResult as $index => $res) {
            if (!isset($res['matches']) || empty($res['matches'])) {
                continue;
            }

            $ids = array_keys($res['matches']);

            $resultItem = array(
                'query'         => $phrasesGroups[$index]['name'],
                'total_found'   => $res['total_found']
            );

            if ($verbose) {
                $fields = array('date');
                if ($verbose == SEARCH_VERBOSE_FULL) {
                    $fields = array_merge($fields, array(
                        'title', 'content', 'source_url', 'source_id'
                    ));
                }
                $resultItem['documents'] = DBQuery::withTable('news')
                    ->getFields($fields)
                    ->where(array('_id' => $ids))
                    ->order('date')
                    ->fetchAll();
            }

            $results[] = $resultItem;
        }

        return $results;
    }
}
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 23.01.13
 * Time: 0:54
 */

class CActionSearch extends CAction
{
    public function action_index()
    {
        $cl = new SphinxClient();
        $cl->SetServer( 'localhost', 9313 );

        // Собственно поиск
        $cl->SetMatchMode( SPH_MATCH_EXTENDED  ); // ищем хотя бы 1 слово из поисковой фразы
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

        $getDocuments = isset($this->postData['verbose']) ? !!$this->postData['verbose'] : false;

        $queries = array();
        foreach ($phrasesGroups as $phraseGroup) {
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

            $dbDocuments = DBQuery::withTable('news')
                ->getFields(array('date', 'source_url'))
                ->where(array('_id' => $ids))
                ->order('date')
                ->fetchAll();

            $results[] = array(
                'query'         => $phrasesGroups[$index]['name'],
                'documents'     => $dbDocuments,
                'total_found'   => $res['total_found']
            );
        }

        return $results;
    }
}
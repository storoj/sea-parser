<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 4:05
 */

class CActionSettings extends CAction
{
    /**
     * @var CRegistryJSON
     */
    private $registryJSON = null;

    public function __construct()
    {
        parent::__construct();
        $this->registryJSON = CRegistryJSON::getInstance();
    }

    public function action_index()
    {
        return $this->registryJSON->getData();
    }

    public function action_phrases()
    {
        return $this->registryJSON->get('phrases', array());
    }

    public function action_phrases_save()
    {
        if (!isset($this->postData['groups'])) {
            $this->setStatus('error', 'Отсутствуют данные о группах');
            return false;
        }

        $groupsData = $this->postData['groups'];
        if (!is_array($groupsData)) {
            $this->setStatus('error', 'Неверные данные о группах');
            return false;
        }

        foreach ($groupsData as $index => $groupData) {
            if (!isset($groupData['name']) || empty($groupData['name'])) {
                $this->setStatus('error', 'Задайте название для группы!');
                return false;
            }
            if (!isset($groupData['phrases'])
                || !is_array($groupData['phrases'])
                || empty($groupData['phrases']))
            {
                $this->setStatus('error', 'Ошибка в данных о фразах!');
                return false;
            }
            $filteredPhrases = array();
            foreach ($groupData['phrases'] as $phrase) {
                $phrase = trim($phrase);
                if (!empty($phrase)) {
                    $filteredPhrases[] = $phrase;
                }
            }
            if (empty($filteredPhrases)) {
                $this->setStatus('error', 'Каждая группа должна содержать хотя бы одну фразу');
                return false;
            }

            $groupsData[$index]['phrases'] = $filteredPhrases;
        }

        $this->registryJSON->invalidate();
        $this->registryJSON->set('phrases', $groupsData);

        if (!$this->registryJSON->Save()) {
            $this->setStatus('error', 'Не удалось сохранить данные');
            return false;
        }
        $this->setStatus('ok', 'Данные сохранены');
        return $groupsData;
    }
    
    public function action_phrases_groups()
    {
        $phrasesGroups = array();
        $phrasesGroupsInfo = $this->registryJSON->get('phrases');
        foreach ($phrasesGroupsInfo as $index => $phrasesGroupInfo) {
            $phrasesGroups[] = array(
                'id'    => $index,
                'name'  => $phrasesGroupInfo['name']
            );
        }
        return $phrasesGroups;
    }
}

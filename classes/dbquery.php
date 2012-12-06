<?

define('DBQUERY_SQL_TEMPLATE_SELECT', 1);
define('DBQUERY_SQL_TEMPLATE_UPDATE', 2);
define('DBQUERY_SQL_TEMPLATE_DELETE', 3);
define('DBQUERY_SQL_TEMPLATE_INSERT', 4);
define('DBQUERY_SQL_TEMPLATE_UNION', 5);

class DBQuery {

    private $tableName  = '';
    private $tableAlias = '';
    private $fields	     = '';
    private $where      = '';
    private $order      = '';
    private $limit      = '';
    private $joins      = '';
    private $groupBy    = '';
    private $unions     = '';
    private $options    = '';
    private $keys       = '';
    private $values     = '';

    private $groupByField = '';
    private $dbResult	= NULL;
    //private $sql		= '';
    public $sql = '';
    private $resultType = NULL;

    private $sqlTemplates = array(
        DBQUERY_SQL_TEMPLATE_SELECT => array(
            'TPL_STRING'	=> 'SELECT {options} {fields} FROM {tableName} {tableAlias} {joins} {where} {groupBy} {order} {limit}',
            'REQUIRED'		=> array('fields', 'tableName')
        ),
        DBQUERY_SQL_TEMPLATE_UPDATE => array(
            'TPL_STRING'	=> 'UPDATE {tableName} {tableAlias} SET {fields} {where}',
            'REQUIRED'		=> array('tableName', 'fields')
        ),
        DBQUERY_SQL_TEMPLATE_DELETE => array(
            'TPL_STRING'	=> 'DELETE FROM {tableName} {where}',
            'REQUIRED'		=> array('tableName')
        ),
        DBQUERY_SQL_TEMPLATE_INSERT => array(
            'TPL_STRING'	=> 'INSERT INTO {tableName} SET {fields}',
            'REQUIRED'		=> array('tableName', 'fields')
        ),
        DBQUERY_SQL_TEMPLATE_UNION => array(
            'TPL_STRING'	=> '{unions} {where} {groupBy} {order} {limit}',
            'REQUIRED'		=> array('unions')
        )
    );

    private $SQL_SELECT_TPL = array(
        'TPL_STRING'	=> 'SELECT {options} {fields} FROM {tableName} {tableAlias} {joins} {where} {groupBy} {order} {limit}',
        'REQUIRED'		=> array('fields', 'tableName')
    );
    private $SQL_UPDATE_TPL = array(
        'TPL_STRING'	=> 'UPDATE {tableName} {tableAlias} SET {fields} {where}',
        'REQUIRED'		=> array('tableName', 'fields')
    );
    private $SQL_DELETE_TPL = array(
        'TPL_STRING'	=> 'DELETE FROM {tableName} {where}',
        'REQUIRED'		=> array('tableName')
    );
    private $SQL_INSERT_TPL = array(
        'TPL_STRING'	=> 'INSERT INTO {tableName} SET {fields}',
        'REQUIRED'		=> array('tableName', 'fields')
    );
    private $SQL_MULTI_INSERT_TPL = array(
        'TPL_STRING'	=> 'INSERT INTO {tableName} {keys} VALUES {values}',
        'REQUIRED'		=> array('tableName', 'values')
    );

    public static function withTable($tableName, $alias = NULL){
        $query = new DBQuery();
        $query->table($tableName, $alias);
        return $query;
    }

    public static function RawQuery($sql){
        $db = DB::getInstance();
        $sql = str_replace('#__', $db->getPrefix(), $sql);
        return $db->query($sql);
    }

    public static function wrapper($field){
        if(is_numeric($field)){
            return $field;
        }
        $field = preg_replace('#`?(\w+)`?\.`?(\w+)`?#', '`$1`.`$2`', $field);
        $field = preg_replace('#(`\w+`)\.(`?(\w+)`?)#', '$1.`$3`', $field);
        $field = preg_replace('#^(\w+)$#', '`$1`', $field);

        return $field;
    }

    private function filterValue($value){
        if($value === NULL){
            return 'NULL';
        }

        if(is_array($value)){
            $value = serialize($value);
        }

        if(!is_numeric($value)){
            return '"'.mysql_real_escape_string($value).'"';
        }

        return $value;
    }

    public function resultType($type){
        if(method_exists($type, 'fillData')){
            $this->resultType = $type;
        }
    }

    public function table($tableName, $alias = NULL){
        $this->tableName = self::wrapper(DB::getInstance()->getFullTableName($tableName));
        if(is_null($alias)){
            $alias = $tableName;
        }
        $this->tableAlias = " AS " . self::wrapper($alias);
        return $this;
    }

    public function call($func, $params){
        /*
         * TODO return result
         * */
        foreach($params as &$param){
            if(!is_numeric($param)){
                $param = '"'.$param.'"';
            }
        }
        $this->sql = 'call '.$func.'('.implode(',', $params).')';
        $this->Query();
        return $this;
    }

    public function getPagerData($page, $perPage, $use_optional = false){
        if ($use_optional) {
            $count = $this->optional_count();
        } else {
            $count = $this->count();
        }

        if($page != 'all'){
            $total_page_num = ceil($count / $perPage);
            if ($page == 'last' || ($total_page_num > 0 && $page > $total_page_num)){
                $page = $total_page_num;
            }

            $offset = ($page - 1) * $perPage;
            $offsetEnd = $offset+$perPage;
            if($offsetEnd > $count){
                $offsetEnd = $count;
            }
            $this->limit($perPage, $offset);
        } else {
            $offset = 0;
            $total_page_num = 1;
            $offsetEnd = $count;
        }

        return array(
            'count'         => $count,
            'pages_count'   => $total_page_num,
            'page'          => $page,
            'perPage'       => $perPage,
            'offsetStart'   => $offset+1,
            'offsetEnd'     => $offsetEnd
        );
    }

    public function getFields($arFields, $add = false){
        if(empty($arFields)){
            $arFields = '*';
        }
        if(is_array($arFields)){
            foreach($arFields as $key => &$field){
                if ($field instanceof DBQuery) {
                    $field = "(".$field->getSQL($this->SQL_SELECT_TPL).")";
                } else {
                    $field = self::wrapper($field);
                }
                if (!is_numeric($key)) {
                    $field .= ' AS '.self::wrapper($key);
                }
            }
            $arFields = implode(', ', $arFields);
        }

        // raw string
        if ($add && !empty($this->fields)) {
            $this->fields .= ',' . $arFields;
        } else {
            $this->fields = $arFields;
        }

        return $this;
    }

    public function setFields($arFields){
        if (is_array($arFields)) {
            $result = array();
            foreach($arFields as $fieldName => $value){
                $fieldName = self::wrapper($fieldName);
                $value = $this->filterValue($value);

                $result[] = $fieldName.'='.$value;
            }

            $this->fields = implode(', ', $result);
        } else {
            $this->fields = $arFields;
        }
        return $this;
    }

    public function setOptions($arOptions) {
        if (is_array($arOptions)) {
            $this->options = implode(' ', $arOptions);
        } else {
            $this->options = $arOptions;
        }

        return $this;
    }

    public function where($where, $filter = true){
        $whereResult = false;
        $conditions = array('>' => '>', '<' => '<', '!' => '<>',
            '_' => 'IN', '<=' => '<=', '>=' => '>=');
        if(!empty($where)){
            if (is_array($where)) {
                $list = array();
                foreach($where as $field => $val) {

                    if (is_numeric($field) && strpos($val, '(') > 0) {
                        $list[] = $val;
                        continue;
                    }

                    $sign = substr($field, 0, 1);
                    $cut = 1;
                    if ($sign == '<' || $sign == '>') {
                        $sign2 = substr($field, 1, 1);
                        if ($sign2 == '=') {
                            $cut = 2;
                            $sign .= $sign2;
                        }
                    }

                    if (array_key_exists($sign, $conditions)) {
                        $field = substr($field, $cut);
                        $sign = $conditions[$sign];
                    } else {
                        $sign = '=';
                    }

                    if ($sign == '@') {
                        if(!empty($val)){
                            $list[] = self::wrapper($field) . ' = ' . self::wrapper($val) . '';
                        }
                    } elseif ($sign == 'IN') {
                        if (is_array($val)) {
                            $val = implode(',', $val);
                        }
                        if(!empty($val)){
                            $list[] = self::wrapper($field) . ' '.$sign.' (' . $val . ')';
                        }
                    } else {
                        if(is_null($val) && ($sign == '=' || $sign == '<>')){
                            if($sign == '='){
                                $list[] = 'ISNULL('.self::wrapper($field).')';
                            } else {
                                $list[] = 'NOT ISNULL('.self::wrapper($field).')';
                            }
                        } else {
                            if($filter){
                                $val = '"'.mysql_real_escape_string($val).'"';
                            }
                            $list[] = self::wrapper($field) . ' '.$sign.' ' . $val . '';
                        }
                    }
                }
                if(!empty($list)){
                    $whereResult = '('.implode(" AND ", $list).')';
                }
            } else {
                $whereResult = '('.$where.')';
            }
        }

        if($whereResult !== false){
            if(empty($this->where)){
                $this->where = "WHERE " . $whereResult;
            } else {
                $this->where .= ' AND ' . $whereResult;
            }
        }

        return $this;
    }

    public function whereLike($field, $value){
        $words = explode(' ', trim($value));
        $conditionList = array();
        foreach($words as $word){
            $word = trim($word);
            if(!empty($word)){
                $conditionList[] = '('.$this->wrapper($field).' LIKE "%'.mysql_real_escape_string($word).'%")';
            }
        }
        if(!empty($conditionList)){
            if(empty($this->where)){
                $this->where = 'WHERE ';
            } else {
                $this->where .= ' AND ';
            }
            $this->where .= '('.implode('OR', $conditionList).')';
        }
        return $this;
    }

    public function order($fieldName, $direction = 'ASC', $table = ''){
        /* TODO must be ordered by multiple fields */
        if ($fieldName != 'RAND()') {
            if(!empty($table)){
                $fieldName = $table.'.'.$fieldName;
            }
            $orderStr = self::wrapper($fieldName).' '.$direction;
        } else {
            $orderStr = $fieldName;
        }

        if(empty($this->order)){
            $this->order = 'ORDER BY '.$orderStr;
        } else {
            $this->order .= ', '.$orderStr;
        }

        return $this;
    }

    public function group($fieldName){
        $this->groupBy = 'GROUP BY '.self::wrapper($fieldName);
        $this->groupByField = $fieldName;
        return $this;
    }

    public function join($table, $on, $type = 'LEFT'){
        if (is_array($table)) {
            foreach($table as $alias => $name) {
                $this->joins .= " " . $type." JOIN ".self::wrapper(DB::getInstance()->getFullTableName($name))
                    ." AS ".self::wrapper($alias);
            }
        } else {
            $this->joins .= " " . $type." JOIN ".self::wrapper(DB::getInstance()->getFullTableName($table))
                ." AS ".self::wrapper($table);
        }

        if(is_array($on)){
            $this->joins .= " ON " . self::wrapper($on[0]) . " = " . self::wrapper($on[1]);
        } else {
            $this->joins .= " ON ".$on;
        }

        return $this;
    }

    public function union(DBQuery $query){
        if(empty($this->unions)){
            $this->unions = '('.$query->getSQL(DBQUERY_SQL_TEMPLATE_SELECT).')';
        } else {
            $this->unions .= ' UNION ('.$query->getSQL(DBQUERY_SQL_TEMPLATE_SELECT).')';
        }
    }

    public function limit($limit, $offset = 0){
        $this->limit = 'LIMIT '.$offset.','.$limit;
        return $this;
    }

    public function getSQL($sqlTemplate){
        if(is_integer($sqlTemplate) && isset($this->sqlTemplates[$sqlTemplate])){
            $sqlTemplate = $this->sqlTemplates[$sqlTemplate];
        }

        foreach($sqlTemplate['REQUIRED'] as $placeholder){
            if(empty($this->$placeholder)){
                # TODO send it to debug
                #echo 'no '.$placeholder;
                return false;
            }
        }
        $sql = $sqlTemplate['TPL_STRING'];
        $toReplace = array('tableName', 'tableAlias', 'where', 'order', 'limit',
            'joins', 'fields', 'groupBy', 'unions', 'options', 'keys', 'values');

        foreach($toReplace as $replacer){
            $sql = str_replace('{'.$replacer.'}', $this->$replacer, $sql);
        }

        return $sql;
    }

    public function Query($sql = NULL){

        if (!is_null($sql)) {
            $this->sql = $sql;
        } elseif (empty($this->sql)){
            return $this;
        }

        $this->dbResult = DB::getInstance()->query($this->sql);
        //return $this;
        return $this->dbResult;
    }

    public function Insert(){
        $this->sql = $this->getSQL($this->SQL_INSERT_TPL);
        $this->Query();

        if(false === $this->dbResult){
            return false;
        }
        return mysql_insert_id();
    }

    public function MultiInsert($keys, $data){
        $insertData = array();
        if(is_array($data)){
            foreach($data as $row){
                $tmpRow = array();
                foreach($keys as $key){
                    $tmpRow[] = isset($row[$key]) ? self::filterValue($row[$key]) : "''";
                }
                $insertData[] = '('.implode(', ', $tmpRow).')';
            }
        }
        foreach($keys as $index => $key){
            $keys[$index] = self::wrapper($key);
        }
        $keys = '('.implode(', ', $keys).')';
        $this->keys = $keys;
        $this->values = implode(', ', $insertData);

        $this->sql = $this->getSQL($this->SQL_MULTI_INSERT_TPL);
        $this->Query();


        return $this->dbResult != false;
    }

    public function Update(){
        $this->sql = $this->getSQL($this->SQL_UPDATE_TPL);
        $this->Query();

        return $this->dbResult != false;
    }

    public function Delete(){
        $this->sql = $this->getSQL($this->SQL_DELETE_TPL);
        $this->Query();

        return $this->dbResult != false;
    }

    public function fillData($row){
        if($this->resultType !== NULL){
            $className = $this->resultType;

            $obj = new $className();
            $obj->fillData($row);

            return $obj;
        }
        return $row;
    }

    public function fetch(){
        if(is_null($this->dbResult)){
            $this->sql = $this->getSQL($this->SQL_SELECT_TPL);

            $sqlHash = md5($this->sql);
            $cached = DB::getInstance()->getCached($sqlHash);

            if($cached !== false){
                return $cached;
            }
            $this->Query();

            if(empty($this->dbResult)){
                return false;
            }
        }
        $result = mysql_fetch_assoc($this->dbResult);
        $result = $this->fillData($result);
        
        //DB::getInstance()->setCached($sqlHash, $result);
        return $result;
    }

    public function fetchAll($groupByField = false) {
        return $this->fetchQuery($this->getSQL(DBQUERY_SQL_TEMPLATE_SELECT), $groupByField);
    }

    public function fetchUnion($groupByField = false) {
        return $this->fetchQuery($this->getSQL(DBQUERY_SQL_TEMPLATE_UNION), $groupByField);
    }

    public function fetchQuery($sql, $groupByField = false) {
        $this->sql = $sql;
        $this->Query();

        if(empty($this->dbResult)){
            return false;
        }

        if (false === $groupByField) {
            for($result = array(); $row = mysql_fetch_assoc($this->dbResult); $result[] = $this->fillData($row));
        } else {
            for($result = array(); $row = mysql_fetch_assoc($this->dbResult); $result[$row[$groupByField]] = $this->fillData($row));
        }

        return $result;
    }

    public function getSingleFieldList($field = 'id') {
        $this->getFields(array($field));

        $this->sql = $this->getSQL($this->SQL_SELECT_TPL);
        $this->Query();

        if(empty($this->dbResult)){
            return false;
        }

        $field_name = explode('.', $field);
        if (isset($field_name[1]) && !empty($field_name[1])) {
            $field = $field_name[1];
        }

        for($result = array(); $row = mysql_fetch_assoc($this->dbResult); $result[] = $row[$field]);

        return $result;
    }

    public function getValue($field) {
        $this->getFields(array($field));

        $this->sql = $this->getSQL($this->SQL_SELECT_TPL);
        $this->Query();

        if(empty($this->dbResult)){
            return false;
        }

        if ($first_row = mysql_fetch_row($this->dbResult)) {
            return $first_row[0];
        }

        return false;
    }

    public function count($what = NULL){
        $oldFields = $this->fields;
        $oldResultType = $this->resultType;
        $oldLimit = $this->limit;
        $oldGroupBy = $this->groupBy;
        if(is_null($what)){
            if(!empty($this->groupByField)){
                $what = 'DISTINCT('.self::wrapper($this->groupByField).')';
            } else {
                $what = '*';
            }
        }

        $this->resultType = NULL;
        $this->limit = NULL;
        $this->groupBy = NULL;
        $this->getFields('COUNT('.$what.') as `count`');
        $data = $this->fetch();

        $this->fields = $oldFields;
        $this->resultType = $oldResultType;
        $this->limit = $oldLimit;
        $this->groupBy = $oldGroupBy;

        if($data !== false){
            $data = $data['count'];
        }
        return $data;
    }

    public function optional_count() {
        $result = $this->Query('SELECT FOUND_ROWS()');
        return intval(mysql_result($result, 0));
    }

    public function increment($field, $num = 1) {
        $this->fields = self::wrapper($field) . " = " . self::wrapper($field) . " + " . $num;
        return $this->Update();
    }
}
?>

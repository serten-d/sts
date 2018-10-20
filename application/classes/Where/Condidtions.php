<?php
namespace Where;

/**
 * Description of Condidtions
 *
 * @author dariu
 */
class Condidtions {
    
    /**
     * prepare serach conditions from params.<br />
     * if columna name has __li sufix, operation LIKE will bwe prepared<br />
     * if columna name has __from sufix, operation column > value will bwe prepared<br />
     * if columna name has __to sufix, operation column < value will bwe prepared<br />
     * in other operation column = value will be prepared<br />
     * 
     * @param array $params expected:
     * [
     *      colum nname => value,
     *      ..
     * ]
     * if columna name has __li sufix, operation LIKE will bwe prepared
     * if columna name has __from sufix, operation column > value will bwe prepared
     * if columna name has __to sufix, operation column < value will bwe prepared
     * in other operation column = value will be prepared
     * @return array expected: [
     *      [
     *          'column' => column name,
     *          'operator' => operation name (LIKE, <, >, ..),
     *          'value' => value
     *      ],
     *      ..
     * ]
     */
    public function prepare($params, $paramsToDbname = [], $skipKeys = [])
    {
        $conditions = [];
        foreach ($params as $key => $value)
        {
            $columnName = $paramsToDbname[$key] ?? $key;
            if(in_array($key, $skipKeys))
            {
                continue;
            }
            switch (TRUE)
            {
                case FALSE !== (strstr($key, '__li')):
                    $conditions[] = $this->likeCondition($columnName, $value);
                    break;
                case FALSE !== (strstr($key, '__from')):
                    $conditions[] = $this->fromCondition($columnName, $value);
                    break;
                case FALSE !== (strstr($key, '__to')):
                    $conditions[] = $this->toCondition($columnName, $value);
                    break;

                default:
                    $conditions[] = $this->equalCondition($columnName, $value);
                    break;
            }
        }
        return $conditions;
    }
    
    /**
     * prepare where condition for LIKE %valeu%
     * 
     * @param string $column
     * @param string $value
     * @return array
     */
    protected function likeCondition($column, $value) {
        return [
            'column' => trim($column),
            'operator' => 'LIKE',
            'value' => '%' . trim($value, '% ') . '%'
        ];
    }
    
    /**
     * prepare where condition for searchin rows with $column value greater than $value
     * 
     * @param string $column
     * @param string $value
     * @return array
     */
    protected function fromCondition($column, $value) {
        return [
            'column' => trim($column),
            'operator' => '>',
            'value' => trim($value)
        ];
    }
    
    /**
     * prepare where condition for searchin rows with $column value less than $value
     * 
     * @param string $column
     * @param string $value
     * @return array
     */
    protected function toCondition($column, $value) {
        return [
            'column' => trim($column),
            'operator' => '<',
            'value' => trim($value)
        ];
    }
    
    /**
     * prepare where condition for searchin rows with $column value less than $value
     * 
     * @param string $column
     * @param string $value
     * @return array
     */
    protected function equalCondition($column, $value) {
        return [
            'column' => trim($column),
            'operator' => '=',
            'value' => trim($value)
        ];
    }
}

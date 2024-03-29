<?php

/*
 * This file is a part of JsonPolicy.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace HyperfGlory\Util\JsonPolicy\Manager;

/**
 * Conditions manager
 *
 * @version 0.0.1
 */
class ConditionManager
{

    /**
     * Map between condition type and method that evaluates the
     * group of conditions
     *
     * @var array
     *
     * @access private
     * @version 0.0.1
     */
    private $_map = array(
        'Between'         => 'evaluateBetweenConditions',
        'Equals'          => 'evaluateEqualsConditions',
        'NotEquals'       => 'evaluateNotEqualsConditions',
        'Greater'         => 'evaluateGreaterConditions',
        'Less'            => 'evaluateLessConditions',
        'GreaterOrEquals' => 'evaluateGreaterOrEqualsConditions',
        'LessOrEquals'    => 'evaluateLessOrEqualsConditions',
        'In'              => 'evaluateInConditions',
        'NotIn'           => 'evaluateNotInConditions',
        'Like'            => 'evaluateLikeConditions',
        'NotLike'         => 'evaluateNotLikeConditions',
        'RegEx'           => 'evaluateRegexConditions'
    );

    /**
     * Construct the condition parser
     *
     * @param array $map Collection of additional conditions
     *
     * @access  public
     * @version 0.0.1
     */
    public function __construct(array $map = [])
    {
        $this->_map = array_merge($this->_map, $map);
    }

    /**
     * Evaluate the group of conditions based on type
     *
     * @param array $conditions List of conditions
     *
     * @return boolean
     *
     * @access public
     * @version 0.0.1
     */
    public function evaluate(array $conditions) : ?bool
    {
        $result   = null;
        $operator = $this->_determineConditionOperator($conditions);

        foreach ($conditions as $type => $group) {
            if (isset($this->_map[$type])) {
                $callback = $this->_map[$type];

                if (is_string($callback) && method_exists($this, $callback)) {
                    $callback = [$this, $callback];
                }

                // Determining logical operator within group
                $group_operator = $this->_determineConditionOperator($group);

                // Evaluating group
                $group_res = $callback($group, $group_operator, $this);

                $result = self::compute($result, $group_res, $operator);
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Evaluate group of BETWEEN conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateBetweenConditions(
        array $conditions, string $operator = 'AND'
    ) {
        $result = null;

        foreach ($conditions as $cnd) {
            $sub_result = null;

            // Convert the right operand into the array of array to cover
            // more complex conditions like [[0,8],[13,15]]
            if (!is_array($cnd['right'][0])) {
                $right_operand = array($cnd['right']);
            } else {
                $right_operand = $cnd['right'];
            }

            foreach ($right_operand as $subset) {
                $min = (is_array($subset) ? array_shift($subset) : $subset);
                $max = (is_array($subset) ? end($subset) : $subset);

                $sub_result = self::compute(
                    $sub_result, ($cnd['left'] >= $min && $cnd['left'] <= $max), 'OR'
                );
            }

            $result = self::compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of EQUALS conditions
     *
     * The values have to be identical
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateEqualsConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = self::compute(
                    $sub_result, ($cnd['left'] === $value), 'OR'
                );
            }

            $result = self::compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of NOT EQUALs conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateNotEqualsConditions(
        array $conditions, string $operator = 'AND'
    ) : bool {
        return !$this->evaluateEqualsConditions($conditions, $operator);
    }

    /**
     * Evaluate group of GREATER THEN conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateGreaterConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = self::compute(
                    $sub_result, ($cnd['left'] > $value), 'OR'
                );
            }

            $result = self::compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of LESS THEN conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateLessConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = self::compute(
                    $sub_result, ($cnd['left'] < $value), 'OR'
                );
            }

            $result = self::compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of GREATER OR EQUALS THEN conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateGreaterOrEqualsConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = self::compute(
                    $sub_result, ($cnd['left'] >= $value), 'OR'
                );
            }

            $result = self::compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of LESS OR EQUALS THEN conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateLessOrEqualsConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub_result = self::compute(
                    $sub_result, ($cnd['left'] <= $value), 'OR'
                );
            }

            $result = self::compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of IN conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateInConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $result = self::compute(
                $result, in_array($cnd['left'], $cnd['right'], true), $operator
            );
        }

        return $result;
    }

    /**
     * Evaluate group of NOT IN conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateNotInConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $result = self::compute(
                $result, !in_array($cnd['left'], $cnd['right'], true), $operator
            );
        }

        return $result;
    }

    /**
     * Evaluate group of LIKE conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateLikeConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub = str_replace(
                    array('\*', '\#'), array('.*', '\\#'), preg_quote($value)
                );

                $sub_result = $this->compute(
                    $sub_result,
                    preg_match('#^' . $sub . '$#ms', $cnd['left']) === 1,
                    'OR'
                );
            }

            $result = self::compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of NOT LIKE conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateNotLikeConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $value) {
                $sub = str_replace(
                    array('\*', '\#'), array('.*', '\\#'), preg_quote($value)
                );

                $sub_result = self::compute(
                    $sub_result,
                    preg_match('#^' . $sub . '$#ms', $cnd['left']) !== 1,
                    'OR'
                );
            }

            $result = self::compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Evaluate group of REGEX conditions
     *
     * @param array  $conditions
     * @param string $operator
     *
     * @return boolean
     *
     * @access protected
     * @version 0.0.1
     */
    protected function evaluateRegexConditions(
        array $conditions, string $operator = 'AND'
    ) : ?bool {
        $result = null;

        foreach ($conditions as $cnd) {
            $sub_result = null;

            foreach($cnd['right'] as $regex) {
                // Check if RegEx is wrapped with forward slashes "/" and if not,
                // wrap it
                if (strpos($regex, '/') !== 0) {
                    $regex = "/{$regex}/";
                }

                $sub_result = self::compute(
                    $sub_result, preg_match($regex, $cnd['left']) === 1, 'OR'
                );
            }

            $result = self::compute($result, $sub_result, $operator);
        }

        return $result;
    }

    /**
     * Determine primary logical operator
     *
     * Based on the reserved "Operator" attribute, determine the how the
     * sub-conditions are going to be logically joined to determine boolean result
     *
     * @param array &$conditions
     *
     * @return string
     *
     * @access private
     * @version 0.0.1
     */
    private function _determineConditionOperator(array &$conditions) : string
    {
        $op = 'AND';

        if (isset($conditions['Operator'])) {
            $op = $conditions['Operator'];

            // Remove this reserved property to avoid it from being used as actual
            // condition
            unset($conditions['Operator']);
        }

        return (in_array($op, array('AND', 'OR'), true) ? $op : 'AND');
    }

    /**
     * Compute the logical expression
     *
     * @param boolean $left
     * @param boolean $right
     * @param string  $operator
     *
     * @return boolean|null
     *
     * @access public
     * @version 0.0.1
     */
    public static function compute(bool $left, bool $right, string $operator) : ?bool
    {
        $result = null;

        if ($left === null) {
            $result = $right;
        } elseif ($operator === 'AND') {
            $result = $left && $right;
        } elseif ($operator === 'OR') {
            $result = $left || $right;
        }

        return $result;
    }

}

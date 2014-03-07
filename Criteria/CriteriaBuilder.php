<?php

/**
 *
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Jean-Philippe CHATEAU <jp.chateau@trepia.fr>
 * @license: GPL
 *
 */

namespace Tms\Bundle\RestBundle\Criteria;

class CriteriaBuilder
{
    /**
     * @var array
     *
     * Example:
     * array('route_name' => array(
     *     'default' => 20,
     *     'maximum' => 100,
     * ))
     */
    private $pagination;

    /**
     * Constructor
     *
     * @param array $pagination
     */
    public function __construct(array $pagination)
    {
        $this->pagination = $pagination;
    }

    /**
     * Clean the criteria according to a list of given parameters and eventually a route name
     *
     * @param array        $parameters
     * @param string|null  $route
     * @return array
     */
    public function clean(array $parameters, $route = null)
    {
        if (!count($parameters)) {
            return $parameters;
        }

        foreach ($parameters as $name => $value) {
            if ('limit' === $name) {
                $parameters[$name] = $this->defineLimitValue($value, $this->guessPaginationByRoute($route));
                continue;
            }

            if (null === $value) {
                unset($parameters[$name]);
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    try {
                        $parameters[$name][$k] = unserialize($v);
                    } catch(\Exception $e) {
                        continue;
                    }
                }
            }
        }

        return $parameters;
    }

    /**
     * Guess Pagination by Route
     *
     * @param string|null $route
     * @return array
     */
    private function guessPaginationByRoute($route = null)
    {
        if (null !== $route && isset($this->pagination[$route])) {
            return $this->pagination[$route];
        }

        return $this->pagination['default_configuration'];
    }

    /**
     * Define the limit value according to the original value and the defined configuration of the pagination
     *
     * @param mixed $originalValue
     * @param array $pagination
     * @return integer
     */
    private function defineLimitValue($originalValue, array $pagination)
    {
        if (null === $originalValue) {
            if ($pagination['default'] > $pagination['maximum']) {
                return $pagination['maximum'];
            }

            return $pagination['default'];
        }

        if (intval($originalValue) > $pagination['maximum']) {
            return $pagination['maximum'];
        }

        return $originalValue;
    }
}

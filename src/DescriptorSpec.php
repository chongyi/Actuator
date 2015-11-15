<?php
/**
 * DescriptorSpec.php
 *
 * Created by Chongyi
 * Date & Time 2015/11/15 19:24
 */

namespace Dybasedev\Actuator;

use Dybasedev\Actuator\Contracts\DescriptorSpecInterface;

class DescriptorSpec implements DescriptorSpecInterface
{
    const STDIN  = 0;
    const STDOUT = 1;
    const STDERR = 2;

    protected $descriptorSpec = [];

    /**
     * @param array|null $descriptorSpec
     */
    public function __construct(array $descriptorSpec = null)
    {
        if (!is_null($descriptorSpec)) {
            $this->descriptorSpec = $descriptorSpec;
        }
    }

    /**
     * @param $descriptor
     * @param $spec
     *
     * @return DescriptorSpec
     */
    public function add($descriptor, $spec)
    {
        $this->descriptorSpec[$descriptor] = $spec;

        return $this;
    }

    /**
     * @param $descriptor
     * @param $spec
     *
     * @return DescriptorSpec
     */
    public function stdIO($descriptor, $spec)
    {
        $params = array_merge([$descriptor], $spec);

        return call_user_func_array([$this, 'add'], $params);
    }

    /**
     * @param $spec
     *
     * @return DescriptorSpec
     */
    public function stdIn($spec)
    {
        $params = is_array($spec) ? $spec : func_get_args();

        return $this->stdIO(static::STDIN, $params);
    }

    /**
     * @param $spec
     *
     * @return DescriptorSpec
     */
    public function stdOut($spec)
    {
        $params = is_array($spec) ? $spec : func_get_args();

        return $this->stdIO(static::STDOUT, $params);
    }

    /**
     * @param $spec
     *
     * @return DescriptorSpec
     */
    public function stdError($spec)
    {
        $params = is_array($spec) ? $spec : func_get_args();

        return $this->stdIO(static::STDERR, $params);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->descriptorSpec;
    }
}
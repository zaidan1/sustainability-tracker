<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/analytics/data/v1alpha/data.proto

namespace Google\Analytics\Data\V1alpha;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A list of segment filter expressions.
 *
 * Generated from protobuf message <code>google.analytics.data.v1alpha.SegmentFilterExpressionList</code>
 */
class SegmentFilterExpressionList extends \Google\Protobuf\Internal\Message
{
    /**
     * The list of segment filter expressions
     *
     * Generated from protobuf field <code>repeated .google.analytics.data.v1alpha.SegmentFilterExpression expressions = 1;</code>
     */
    private $expressions;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<\Google\Analytics\Data\V1alpha\SegmentFilterExpression>|\Google\Protobuf\Internal\RepeatedField $expressions
     *           The list of segment filter expressions
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Analytics\Data\V1Alpha\Data::initOnce();
        parent::__construct($data);
    }

    /**
     * The list of segment filter expressions
     *
     * Generated from protobuf field <code>repeated .google.analytics.data.v1alpha.SegmentFilterExpression expressions = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getExpressions()
    {
        return $this->expressions;
    }

    /**
     * The list of segment filter expressions
     *
     * Generated from protobuf field <code>repeated .google.analytics.data.v1alpha.SegmentFilterExpression expressions = 1;</code>
     * @param array<\Google\Analytics\Data\V1alpha\SegmentFilterExpression>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setExpressions($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Analytics\Data\V1alpha\SegmentFilterExpression::class);
        $this->expressions = $arr;

        return $this;
    }

}

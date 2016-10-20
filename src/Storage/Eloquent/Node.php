<?php

namespace GigaAI\Storage\Eloquent;

class Node extends \Illuminate\Database\Eloquent\Model
{
    public $table = 'bot_nodes';

    protected $fillable = ['instance_id', 'pattern', 'answers', 'wait', 'sources', 'type', 'status'];

    protected $casts = [
        'answers' => 'array'
    ];

    public static function extractAnswers($nodes)
    {
        $output = [];

        foreach ($nodes as $node) {

            // If default, then return only first row fetched!
            if ($node->type === 'default')
                return ['default' => $node->answers];

            if ( ! isset($output[$node->pattern]))
                $output[$node->pattern] = [];

            $output[$node->pattern] = $node->answers;
        }

        return $output;
    }

    /**
     * Get node by node type and pattern
     *
     * @param $type
     * @param $pattern
     * @return Node[]
     */
    public static function findByTypeAndPattern($type = '', $pattern = '')
    {
        $where          = '1 = 1';
        $placeholder    = [];

        if ( ! empty($type)) {
            $where                  .= ' AND type = :type';
            $placeholder[':type']   = $type;
        }

        if ( ! empty($pattern)) {
            $placeholder[':pattern'] = $pattern;

            // Intended Action. We'll get first row.
            if ($pattern[0] === '@') {
                $where .= ' AND pattern = :pattern';
            }
            else {
                $where .= " AND (:pattern RLIKE pattern OR :pattern2 LIKE pattern)";
                $placeholder[':pattern2'] = $pattern;
            }
        }

        return Node::whereRaw($where, $placeholder)
            ->get(['type', 'pattern', 'answers']);
    }

    public static function getAnswersByTypeAndPatterns($type = '', $pattern = '')
    {
        $nodes = self::findByTypeAndPattern($type, $pattern);

        return self::extractAnswers($nodes);
    }
}

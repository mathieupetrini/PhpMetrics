<?php
namespace Hal\Metric\Helper;

use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;

/**
 * Class RoleOfMethodDetector
 * @package Hal\Metric\Helper
 */
class RoleOfMethodDetector
{
    /**
     * @var array
     */
    private $fingerprints = [
        'getter' => [
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Return_',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Return_',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Return_',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name\\FullyQualified'
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Return_',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\NullableType',
                'PhpParser\\Node\\Name\\FullyQualified'
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Return_',
                'PhpParser\\Node\\Expr\\MethodCall',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable'
            ]
        ],
        'setter' => [
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Param',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Name',
            ],
            // nicik/php-parser:^4
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Expression',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Expr\\Variable',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Expression',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Expression',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Expr\\Variable'
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Expression',
                'PhpParser\\Node\\Expr\\Assign',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name\\FullyQualified'
            ],

            //adder
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Expression',
                'PhpParser\\Node\\Expr\\MethodCall',
                'PhpParser\\Node\\Arg',
                'PhpParser\\Node\\Expr\\New_',
                'PhpParser\\Node\\Arg',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Arg',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name\\FullyQualified',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name\\FullyQualified',
            ],
            [
                'PhpParser\\Node\\Stmt\\ClassMethod',
                'PhpParser\\Node\\Stmt\\Return_',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Stmt\\Expression',
                'PhpParser\\Node\\Expr\\MethodCall',
                'PhpParser\\Node\\Arg',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Expr\\PropertyFetch',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name\\FullyQualified',
                'PhpParser\\Node\\Param',
                'PhpParser\\Node\\Expr\\Variable',
                'PhpParser\\Node\\Name\\FullyQualified'
            ]
        ]
    ];

    /**
     * @param $node
     * @return string|null
     */
    public function detects($node)
    {
        if (! $node instanceof ClassMethod) {
            return null;
        }

        // build a fingerprint of the given method
        $fingerprintOfMethod = [];
        iterate_over_node($node, function ($node) use (&$fingerprintOfMethod) {

            // avoid identifier (php-parser:^4)
            if ($node instanceof Identifier) {
                return;
            }

            // avoid cast
            if ($node instanceof Cast) {
                return;
            }

            // avoid fluent interface
            if ($node instanceof Return_ && $node->expr instanceof Variable && $node->expr->name === 'this') {
                unset($fingerprintOfMethod[sizeof($fingerprintOfMethod) - 1]);
                return;
            }

            $fingerprintOfMethod[] = get_class($node);
        });
        $fingerprintOfMethod = array_reverse($fingerprintOfMethod);

        // compare with database of fingerprints
        foreach ($this->fingerprints as $type => $fingerprints) {
            if (in_array($fingerprintOfMethod, $fingerprints, true)) {
                return $type;
            }
        }

        return null;
    }
}

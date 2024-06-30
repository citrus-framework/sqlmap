<?php

declare(strict_types=1);

return [
    'preset' => 'default',
    'exclude' => [
    ],
    'add' => [
        NunoMaduro\PhpInsights\Domain\Metrics\Code\Code::class => [
            // Code
            SlevomatCodingStandard\Sniffs\ControlStructures\RequireYodaComparisonSniff::class,
        ],
    ],
    'remove' => [
        // Code
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenGlobals::class,
        SlevomatCodingStandard\Sniffs\Classes\ForbiddenPublicPropertySniff::class,
        NunoMaduro\PhpInsights\Domain\Sniffs\ForbiddenSetterSniff::class,
        PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer::class,
        PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\UselessOverridingMethodSniff::class,
        SlevomatCodingStandard\Sniffs\Commenting\UselessInheritDocCommentSniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\LanguageConstructWithParenthesesSniff::class,
        SlevomatCodingStandard\Sniffs\Functions\StaticClosureSniff::class,
        SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff::class,

        //  Architecture
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits::class,
        SlevomatCodingStandard\Sniffs\Classes\SuperfluousExceptionNamingSniff::class,

        // Style
        PHP_CodeSniffer\Standards\Generic\Sniffs\WhiteSpace\ArbitraryParenthesesSpacingSniff::class,
        PHP_CodeSniffer\Standards\PSR2\Sniffs\ControlStructures\ElseIfDeclarationSniff::class,
    ],
    'config' => [
        // Architecture
        SlevomatCodingStandard\Sniffs\Functions\FunctionLengthSniff::class => [
            'maxLinesLength' => 32,
        ],

        // Style
        PhpCsFixer\Fixer\Basic\BracesFixer::class => [
            'allow_single_line_closure' => false,
            'position_after_anonymous_constructs' => 'same',
            'position_after_control_structures' => 'next',
            'position_after_functions_and_oop_constructs' => 'next',
        ],
        PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer::class => [
            'operators' => [
                '=>' => 'align_single_space',
            ],
            'default' => 'single_space', // default fix strategy: possibles values ['align', 'align_single_space', 'align_single_space_minimal', 'single_space', 'no_space', null]
        ],
        PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer::class => [
            'tokens' => [], // possibles values ['break', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block', 'return', 'square_brace_block', 'switch', 'throw', 'use', 'use_trait']
        ],
        PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 120,
            'absoluteLineLimit' => 120,
            'ignoreComments' => true,
        ],
        SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff::class => [
            'linesCountBeforeFirstContent' => 0,
            'linesCountBetweenDescriptionAndAnnotations' => 1,
            'linesCountBetweenDifferentAnnotationsTypes' => 0,
            'linesCountBetweenAnnotationsGroups' => 0,
            'linesCountAfterLastContent' => 0,
            'annotationsGroups' => [],
        ],
    ],
];

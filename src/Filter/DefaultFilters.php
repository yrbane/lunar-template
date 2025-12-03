<?php

declare(strict_types=1);

namespace Lunar\Template\Filter;

use Lunar\Template\Filter\Array\ChunkFilter;
use Lunar\Template\Filter\Array\FilterArrayFilter;
use Lunar\Template\Filter\Array\FirstFilter;
use Lunar\Template\Filter\Array\GroupByFilter;
use Lunar\Template\Filter\Array\JoinFilter;
use Lunar\Template\Filter\Array\KeysFilter;
use Lunar\Template\Filter\Array\LastFilter;
use Lunar\Template\Filter\Array\LengthFilter;
use Lunar\Template\Filter\Array\MapFilter;
use Lunar\Template\Filter\Array\MergeFilter;
use Lunar\Template\Filter\Array\PluckFilter;
use Lunar\Template\Filter\Array\RandomFilter;
use Lunar\Template\Filter\Array\ShuffleFilter;
use Lunar\Template\Filter\Array\SliceFilter;
use Lunar\Template\Filter\Array\SortFilter;
use Lunar\Template\Filter\Array\UniqueFilter;
use Lunar\Template\Filter\Array\ValuesFilter;
use Lunar\Template\Filter\Date\AgoFilter;
use Lunar\Template\Filter\Date\DateFilter;
use Lunar\Template\Filter\Date\RelativeDateFilter;
use Lunar\Template\Filter\Encoding\Base64DecodeFilter;
use Lunar\Template\Filter\Encoding\Base64EncodeFilter;
use Lunar\Template\Filter\Encoding\JsonDecodeFilter;
use Lunar\Template\Filter\Encoding\JsonEncodeFilter;
use Lunar\Template\Filter\Encoding\Md5Filter;
use Lunar\Template\Filter\Encoding\Sha1Filter;
use Lunar\Template\Filter\Encoding\Sha256Filter;
use Lunar\Template\Filter\Encoding\UrlDecodeFilter;
use Lunar\Template\Filter\Encoding\UrlEncodeFilter;
use Lunar\Template\Filter\Html\AbbrFilter;
use Lunar\Template\Filter\Html\AFilter;
use Lunar\Template\Filter\Html\AnchorFilter;
use Lunar\Template\Filter\Html\AttributesFilter;
use Lunar\Template\Filter\Html\AudioFilter;
use Lunar\Template\Filter\Html\BadgeFilter;
use Lunar\Template\Filter\Html\BlockquoteFilter;
use Lunar\Template\Filter\Html\ButtonFilter;
use Lunar\Template\Filter\Html\ClassListFilter;
use Lunar\Template\Filter\Html\CodeFilter;
use Lunar\Template\Filter\Html\DivFilter;
use Lunar\Template\Filter\Html\EmFilter;
use Lunar\Template\Filter\Html\EscapeFilter;
use Lunar\Template\Filter\Html\ExcerptHtmlFilter;
use Lunar\Template\Filter\Html\HeadingFilter;
use Lunar\Template\Filter\Html\HFilter;
use Lunar\Template\Filter\Html\HighlightFilter;
use Lunar\Template\Filter\Html\IframeFilter;
use Lunar\Template\Filter\Html\ImgFilter;
use Lunar\Template\Filter\Html\LinkifyFilter;
use Lunar\Template\Filter\Html\ListHtmlFilter;
use Lunar\Template\Filter\Html\MarkdownFilter;
use Lunar\Template\Filter\Html\MeterFilter;
use Lunar\Template\Filter\Html\Nl2brFilter;
use Lunar\Template\Filter\Html\ParagraphFilter;
use Lunar\Template\Filter\Html\PFilter;
use Lunar\Template\Filter\Html\PreFilter;
use Lunar\Template\Filter\Html\ProgressFilter;
use Lunar\Template\Filter\Html\RawFilter;
use Lunar\Template\Filter\Html\SmallFilter;
use Lunar\Template\Filter\Html\SpacelessFilter;
use Lunar\Template\Filter\Html\SpanFilter;
use Lunar\Template\Filter\Html\StripTagsFilter;
use Lunar\Template\Filter\Html\StrongFilter;
use Lunar\Template\Filter\Html\TableFilter;
use Lunar\Template\Filter\Html\TimeFilter;
use Lunar\Template\Filter\Html\VideoFilter;
use Lunar\Template\Filter\Html\WrapFilter;
use Lunar\Template\Filter\Number\AbsFilter;
use Lunar\Template\Filter\Number\CeilFilter;
use Lunar\Template\Filter\Number\CurrencyFilter;
use Lunar\Template\Filter\Number\FilesizeFilter;
use Lunar\Template\Filter\Number\FloorFilter;
use Lunar\Template\Filter\Number\NumberFormatFilter;
use Lunar\Template\Filter\Number\OrdinalFilter;
use Lunar\Template\Filter\Number\PercentFilter;
use Lunar\Template\Filter\Number\RoundFilter;
use Lunar\Template\Filter\String\CapitalizeFilter;
use Lunar\Template\Filter\String\ExcerptFilter;
use Lunar\Template\Filter\String\LowerFilter;
use Lunar\Template\Filter\String\LtrimFilter;
use Lunar\Template\Filter\String\PadLeftFilter;
use Lunar\Template\Filter\String\PadRightFilter;
use Lunar\Template\Filter\String\RepeatFilter;
use Lunar\Template\Filter\String\ReplaceFilter;
use Lunar\Template\Filter\String\ReverseFilter;
use Lunar\Template\Filter\String\RtrimFilter;
use Lunar\Template\Filter\String\SlugFilter;
use Lunar\Template\Filter\String\SplitFilter;
use Lunar\Template\Filter\String\TitleFilter;
use Lunar\Template\Filter\String\TrimFilter;
use Lunar\Template\Filter\String\TruncateFilter;
use Lunar\Template\Filter\String\UpperFilter;
use Lunar\Template\Filter\String\WordwrapFilter;

/**
 * Registers all default filters into a FilterRegistry.
 */
final class DefaultFilters
{
    /**
     * Register all default filters.
     */
    public static function register(FilterRegistry $registry): FilterRegistry
    {
        // String filters
        $registry->registerInstance(new UpperFilter());
        $registry->registerInstance(new LowerFilter());
        $registry->registerInstance(new CapitalizeFilter());
        $registry->registerInstance(new TitleFilter());
        $registry->registerInstance(new TrimFilter());
        $registry->registerInstance(new LtrimFilter());
        $registry->registerInstance(new RtrimFilter());
        $registry->registerInstance(new SlugFilter());
        $registry->registerInstance(new TruncateFilter());
        $registry->registerInstance(new WordwrapFilter());
        $registry->registerInstance(new ReverseFilter());
        $registry->registerInstance(new RepeatFilter());
        $registry->registerInstance(new PadLeftFilter());
        $registry->registerInstance(new PadRightFilter());
        $registry->registerInstance(new ReplaceFilter());
        $registry->registerInstance(new SplitFilter());
        $registry->registerInstance(new ExcerptFilter());

        // Number filters
        $registry->registerInstance(new NumberFormatFilter());
        $registry->registerInstance(new RoundFilter());
        $registry->registerInstance(new FloorFilter());
        $registry->registerInstance(new CeilFilter());
        $registry->registerInstance(new AbsFilter());
        $registry->registerInstance(new CurrencyFilter());
        $registry->registerInstance(new PercentFilter());
        $registry->registerInstance(new OrdinalFilter());
        $registry->registerInstance(new FilesizeFilter());

        // Array filters
        $registry->registerInstance(new FirstFilter());
        $registry->registerInstance(new LastFilter());
        $registry->registerInstance(new LengthFilter());
        $registry->registerInstance(new KeysFilter());
        $registry->registerInstance(new ValuesFilter());
        $registry->registerInstance(new SortFilter());
        $registry->registerInstance(new SliceFilter());
        $registry->registerInstance(new MergeFilter());
        $registry->registerInstance(new UniqueFilter());
        $registry->registerInstance(new JoinFilter());
        $registry->registerInstance(new ChunkFilter());
        $registry->registerInstance(new PluckFilter());
        $registry->registerInstance(new FilterArrayFilter());
        $registry->registerInstance(new MapFilter($registry));
        $registry->registerInstance(new GroupByFilter());
        $registry->registerInstance(new RandomFilter());
        $registry->registerInstance(new ShuffleFilter());

        // Date filters
        $registry->registerInstance(new DateFilter());
        $registry->registerInstance(new AgoFilter());
        $registry->registerInstance(new RelativeDateFilter());

        // Encoding filters
        $registry->registerInstance(new Base64EncodeFilter());
        $registry->registerInstance(new Base64DecodeFilter());
        $registry->registerInstance(new UrlEncodeFilter());
        $registry->registerInstance(new UrlDecodeFilter());
        $registry->registerInstance(new JsonEncodeFilter());
        $registry->registerInstance(new JsonDecodeFilter());
        $registry->registerInstance(new Md5Filter());
        $registry->registerInstance(new Sha1Filter());
        $registry->registerInstance(new Sha256Filter());

        // HTML filters
        $registry->registerInstance(new RawFilter());
        $registry->registerInstance(new EscapeFilter());
        $registry->registerInstance(new StripTagsFilter());
        $registry->registerInstance(new Nl2brFilter());
        $registry->registerInstance(new SpacelessFilter());

        // HTML formatting filters
        $registry->registerInstance(new MarkdownFilter());
        $registry->registerInstance(new LinkifyFilter());
        $registry->registerInstance(new ListHtmlFilter());
        $registry->registerInstance(new TableFilter());
        $registry->registerInstance(new AttributesFilter());
        $registry->registerInstance(new WrapFilter());
        $registry->registerInstance(new HighlightFilter());
        $registry->registerInstance(new ParagraphFilter());
        $registry->registerInstance(new HeadingFilter());
        $registry->registerInstance(new AnchorFilter());
        $registry->registerInstance(new ExcerptHtmlFilter());
        $registry->registerInstance(new ClassListFilter());

        // HTML element filters
        $registry->registerInstance(new DivFilter());
        $registry->registerInstance(new SpanFilter());
        $registry->registerInstance(new StrongFilter());
        $registry->registerInstance(new EmFilter());
        $registry->registerInstance(new SmallFilter());
        $registry->registerInstance(new CodeFilter());
        $registry->registerInstance(new PreFilter());
        $registry->registerInstance(new BlockquoteFilter());
        $registry->registerInstance(new AbbrFilter());
        $registry->registerInstance(new TimeFilter());
        $registry->registerInstance(new ImgFilter());
        $registry->registerInstance(new VideoFilter());
        $registry->registerInstance(new AudioFilter());
        $registry->registerInstance(new IframeFilter());
        $registry->registerInstance(new ProgressFilter());
        $registry->registerInstance(new MeterFilter());
        $registry->registerInstance(new BadgeFilter());
        $registry->registerInstance(new ButtonFilter());

        // Aliases
        $registry->registerInstance(new PFilter());
        $registry->registerInstance(new HFilter());
        $registry->registerInstance(new AFilter());

        return $registry;
    }

    /**
     * Create a new registry with all default filters.
     */
    public static function create(): FilterRegistry
    {
        return self::register(new FilterRegistry());
    }
}

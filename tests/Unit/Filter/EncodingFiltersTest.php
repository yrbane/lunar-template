<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Filter\Encoding\Base64DecodeFilter;
use Lunar\Template\Filter\Encoding\Base64EncodeFilter;
use Lunar\Template\Filter\Encoding\JsonDecodeFilter;
use Lunar\Template\Filter\Encoding\JsonEncodeFilter;
use Lunar\Template\Filter\Encoding\Md5Filter;
use Lunar\Template\Filter\Encoding\Sha1Filter;
use Lunar\Template\Filter\Encoding\Sha256Filter;
use Lunar\Template\Filter\Encoding\UrlDecodeFilter;
use Lunar\Template\Filter\Encoding\UrlEncodeFilter;
use PHPUnit\Framework\TestCase;

class EncodingFiltersTest extends TestCase
{
    public function testBase64EncodeFilter(): void
    {
        $filter = new Base64EncodeFilter();

        $this->assertSame('base64_encode', $filter->getName());
        $this->assertSame('SGVsbG8gV29ybGQ=', $filter->apply('Hello World'));
    }

    public function testBase64DecodeFilter(): void
    {
        $filter = new Base64DecodeFilter();

        $this->assertSame('base64_decode', $filter->getName());
        $this->assertSame('Hello World', $filter->apply('SGVsbG8gV29ybGQ='));
        $this->assertSame('', $filter->apply('!!!invalid!!!'));
    }

    public function testUrlEncodeFilter(): void
    {
        $filter = new UrlEncodeFilter();

        $this->assertSame('url_encode', $filter->getName());
        $this->assertSame('hello+world', $filter->apply('hello world'));
        $this->assertSame('hello%20world', $filter->apply('hello world', [true]));
    }

    public function testUrlDecodeFilter(): void
    {
        $filter = new UrlDecodeFilter();

        $this->assertSame('url_decode', $filter->getName());
        $this->assertSame('hello world', $filter->apply('hello+world'));
        $this->assertSame('hello world', $filter->apply('hello%20world', [true]));
    }

    public function testJsonEncodeFilter(): void
    {
        $filter = new JsonEncodeFilter();

        $this->assertSame('json_encode', $filter->getName());
        $this->assertSame('{"name":"test"}', $filter->apply(['name' => 'test']));

        $pretty = $filter->apply(['name' => 'test'], [true]);
        $this->assertStringContainsString("\n", $pretty);
    }

    public function testJsonEncodeFilterInvalid(): void
    {
        $filter = new JsonEncodeFilter();

        // Create a resource which cannot be JSON encoded
        $resource = fopen('php://memory', 'r');
        $this->assertNotFalse($resource);

        $result = $filter->apply($resource);
        fclose($resource);

        $this->assertSame('{}', $result);
    }

    public function testJsonDecodeFilter(): void
    {
        $filter = new JsonDecodeFilter();

        $this->assertSame('json_decode', $filter->getName());
        $this->assertSame(['name' => 'test'], $filter->apply('{"name":"test"}'));
        $this->assertNull($filter->apply(''));
        $this->assertNull($filter->apply('invalid json'));
        $this->assertNull($filter->apply('"scalar"'));
    }

    public function testMd5Filter(): void
    {
        $filter = new Md5Filter();

        $this->assertSame('md5', $filter->getName());
        $this->assertSame('b10a8db164e0754105b7a99be72e3fe5', $filter->apply('Hello World'));
    }

    public function testSha1Filter(): void
    {
        $filter = new Sha1Filter();

        $this->assertSame('sha1', $filter->getName());
        $this->assertSame('0a4d55a8d778e5022fab701977c5d840bbc486d0', $filter->apply('Hello World'));
    }

    public function testSha256Filter(): void
    {
        $filter = new Sha256Filter();

        $this->assertSame('sha256', $filter->getName());
        $this->assertSame(
            'a591a6d40bf420404a011733cfb7b190d62c65bf0bcda32b57b277d9ad9f146e',
            $filter->apply('Hello World'),
        );
    }
}

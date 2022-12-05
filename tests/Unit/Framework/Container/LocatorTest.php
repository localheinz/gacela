<?php

declare(strict_types=1);

namespace GacelaTest\Unit\Framework\Container;

use Gacela\Framework\Container\Locator;
use GacelaTest\Fixtures\StringValue;
use PHPUnit\Framework\TestCase;

final class LocatorTest extends TestCase
{
    private Locator $locator;

    public function setUp(): void
    {
        Locator::resetInstance();
        $this->locator = Locator::getInstance();
    }

    public function tearDown(): void
    {
        Locator::resetInstance();
    }

    public function test_get_concrete_class(): void
    {
        /** @var StringValue $stringValue */
        $stringValue = $this->locator->get(StringValue::class);
        self::assertInstanceOf(StringValue::class, $stringValue);
        self::assertSame('', $stringValue->value());
        $stringValue->setValue('updated value');

        /** @var StringValue $stringValue2 */
        $stringValue2 = $this->locator->get(StringValue::class);
        self::assertSame('updated value', $stringValue2->value());
    }

    public function test_get_null_from_non_existing_class(): void
    {
        /** @var null $nullValue */
        $nullValue = $this->locator->get('NonExistingClass');
        self::assertNull($nullValue);
    }
}

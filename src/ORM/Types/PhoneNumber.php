<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber as PhoneNumberBase;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumber extends Type
{
    /**
     * Phone number type name.
     */
    public const NAME = 'phone_number';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if (method_exists($platform, 'getStringTypeDeclarationSQL')) {
            return $platform->getStringTypeDeclarationSQL(['length' => 35]);
        }

        return '';
    }

    /**
     * @throws ConversionException
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof PhoneNumberBase) {
            throw new ConversionException('Expected \libphonenumber\PhoneNumber, got ' . gettype($value));
        }

        $util = PhoneNumberUtil::getInstance();

        return $util->format($value, PhoneNumberFormat::E164);
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value || $value instanceof PhoneNumberBase) {
            return $value;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            return $util->parse($value, PhoneNumberUtil::UNKNOWN_REGION);
        } catch (NumberParseException $e) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

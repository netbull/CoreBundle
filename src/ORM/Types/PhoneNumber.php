<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\NumberParseException;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use libphonenumber\PhoneNumber as PhoneNumberBase;

class PhoneNumber extends Type
{
    /**
     * Phone number type name.
     */
    const NAME = 'phone_number';

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL(['length' => 35]);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return string|null
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
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
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return mixed
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
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

    /**
     * @param AbstractPlatform $platform
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}

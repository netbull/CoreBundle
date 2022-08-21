<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class Json extends Type
{
    const JSON = 'json';

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     * @param AbstractPlatform $platform
     * @return false|string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return json_encode($value);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::JSON;
    }

    /**
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
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

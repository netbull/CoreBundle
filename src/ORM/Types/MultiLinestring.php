<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Exception;
use geoPHP;

class MultiLinestring extends Type
{
    public const MULTILINESTRING = 'multilinestring';

    public function getSqlDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'MULTILINESTRING';
    }

    /**
     * @throws Exception
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (is_null($value)) {
            return '';
        }
        if (!str_contains(strtolower($value), 'multilinestring')) {
            throw new Exception('This is not a MultiLine!');
        }

        $poly = geoPHP::load($value);

        if (!$poly->checkValidity()) {
            throw new Exception('The shape is not a valid MultiLine ' . $value);
        }

        return $poly;
    }

    /**
     * @throws Exception
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (is_null($value)) {
            return '';
        }

        if (!str_contains(strtolower($value), 'multilinestring')) {
            throw new Exception('This is not a polygon!');
        }

        $poly = geoPHP::load($value);

        if (!$poly->checkValidity()) {
            throw new Exception('The shape is not a valid MultiLine ' . $value);
        }

        return $value;
    }

    public function canRequireSQLConversion(): bool
    {
        return true;
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        return sprintf('ST_GeomFromText(%s)', $sqlExpr);
    }

    public function convertToPHPValueSQL($sqlExpr, $platform): string
    {
        return sprintf('ST_AsText(%s)', $sqlExpr);
    }

    public function getName(): string
    {
        return self::MULTILINESTRING; // modify to match your constant name
    }
}

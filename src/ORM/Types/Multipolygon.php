<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Exception;
use geoPHP;

class Multipolygon extends Type
{
    public const MULTIPOLYGON = 'multipolygon';

    public function getSqlDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'MULTIPOLYGON';
    }

    /**
     * @throws Exception
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (is_null($value)) {
            return '';
        }

        if (!str_contains(strtolower($value), 'multipolygon') && !str_contains(strtolower($value), 'polygon')) {
            throw new Exception('This is not a MultiPolygon!');
        }

        $poly = geoPHP::load($value);

        if (!$poly->checkValidity() && !is_null($poly->checkValidity())) {
            throw new Exception('The shape is not a valid MultiPolygon ' . $value);
        }

        return $value;
    }

    /**
     * @throws Exception
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (is_null($value)) {
            return '';
        }

        if (!str_contains(strtolower($value), 'multipolygon') && !str_contains(strtolower($value), 'polygon')) {
            throw new Exception('This is not a MultiPolygon!');
        }

        $poly = geoPHP::load($value);

        if (!$poly->checkValidity() && !is_null($poly->checkValidity())) {
            throw new Exception('The shape is not a valid MultiPolygon' . $value);
        }

        return $poly;
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
        return self::MULTIPOLYGON;
    }
}

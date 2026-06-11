<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Exception;
use geoPHP;

class Geometry extends Type
{
    public const GEOMETRY = 'geometry';

    public function getSqlDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'GEOMETRY';
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
            throw new Exception('This is not a Geometry!');
        }

        $geometry = geoPHP::load($value);

        if (!$geometry->checkValidity() && !is_null($geometry->checkValidity())) {
            throw new Exception('The shape is not a valid Geometry ' . $value);
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

        $geometry = geoPHP::load($value);

        if (!$geometry->checkValidity() && !is_null($geometry->checkValidity())) {
            throw new Exception('The shape is not a valid Geometry ' . $value);
        }

        return $geometry;
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
        return self::GEOMETRY;
    }
}

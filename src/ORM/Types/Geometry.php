<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Exception;
use geoPHP;

class Geometry extends Type
{
    const GEOMETRY = 'geometry';

    /**
     * @param array $column
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSqlDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'GEOMETRY';
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return mixed
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
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return mixed
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

    /**
     * @return bool
     */
    public function canRequireSQLConversion(): bool
    {
        return true;
    }

    /**
     * @param $sqlExpr
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        return sprintf('ST_GeomFromText(%s)', $sqlExpr);
    }

    /**
     * @param $sqlExpr
     * @param $platform
     * @return string
     */
    public function convertToPHPValueSQL($sqlExpr, $platform): string
    {
        return sprintf('ST_AsText(%s)', $sqlExpr);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::GEOMETRY;
    }
}

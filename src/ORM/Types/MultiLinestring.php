<?php

namespace NetBull\CoreBundle\ORM\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Exception;
use geoPHP;

class MultiLinestring extends Type
{
    const MULTILINESTRING = 'multilinestring';

    /**
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'MULTILINESTRING';
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return mixed|string
     * @throws Exception
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return '';
        }
        if (false === strpos(strtolower($value), 'multilinestring')) {
            throw new Exception('This is not a MultiLine!');
        }

        $poly = geoPHP::load($value);

        if (!$poly->checkValidity()) {
            throw new Exception('The shape is not a valid MultiLine ' . $value);
        }

        return $poly;
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return mixed|string
     * @throws Exception
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (is_null($value)) {
            return '';
        }

        if (false === strpos(strtolower($value), 'multilinestring')) {
            throw new Exception("This is not a polygon!");
        }

        $poly = geoPHP::load($value);

        if (!$poly->checkValidity()) {
            throw new Exception('The shape is not a valid MultiLine ' . $value);
        }

        return $value;
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
        return self::MULTILINESTRING; // modify to match your constant name
    }
}

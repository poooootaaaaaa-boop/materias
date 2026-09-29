<?php

namespace App\Support;

class LocationCatalog
{
    public static function countries(): array
    {
        return ['Argentina', 'Bolivia', 'Brasil', 'Chile', 'Colombia', 'Costa Rica', 'Cuba', 'Ecuador', 'El Salvador', 'España', 'Estados Unidos', 'Guatemala', 'Honduras', 'México', 'Nicaragua', 'Panamá', 'Paraguay', 'Perú', 'Puerto Rico', 'República Dominicana', 'Uruguay', 'Venezuela'];
    }

    public static function states(string $country): array
    {
        return match ($country) {
            'México' => ['Aguascalientes', 'Baja California', 'Chiapas', 'Ciudad de México', 'Jalisco', 'Nuevo León', 'Oaxaca', 'Puebla', 'Querétaro', 'Quintana Roo', 'Sonora', 'Veracruz', 'Yucatán'],
            'Colombia' => ['Antioquia', 'Atlántico', 'Bogotá D.C.', 'Bolívar', 'Boyacá', 'Cundinamarca', 'Nariño', 'Santander', 'Valle del Cauca'],
            'Argentina' => ['Buenos Aires', 'Córdoba', 'Mendoza', 'Santa Fe', 'Tucumán'],
            'Perú' => ['Arequipa', 'Cusco', 'La Libertad', 'Lima', 'Piura', 'Puno'],
            'Chile' => ['Antofagasta', 'Araucanía', 'Biobío', 'Coquimbo', 'Los Lagos', 'Metropolitana de Santiago', 'Valparaíso'],
            'España' => ['Andalucía', 'Aragón', 'Asturias', 'Cataluña', 'Comunidad de Madrid', 'Galicia', 'País Vasco', 'Valencia'],
            'Estados Unidos' => ['California', 'Florida', 'Illinois', 'New York', 'Texas', 'Washington'],
            default => [],
        };
    }

    public static function canonical(string $value, array $options): ?string
    {
        $normalized = self::normalize($value);
        foreach ($options as $option) {
            if ($normalized === self::normalize($option)) return $option;
        }
        return null;
    }

    private static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        return strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }
}
<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use JsonException;

class ScholarshipText
{
    public const PREFIX = 'scholarmatch-rich-text-v1:';

    public static function document(?string $value): array
    {
        if (str_starts_with($value ?? '', self::PREFIX)) {
            try {
                $document = json_decode(substr($value, strlen(self::PREFIX)), true, 64, JSON_THROW_ON_ERROR);
                if (is_array($document) && ($document['type'] ?? null) === 'doc') {
                    return self::cleanNode($document);
                }
            } catch (JsonException) {
                // Preserve an unrecognised legacy value as escaped text.
            }
        }

        return [
            'type' => 'doc',
            'content' => array_map(fn ($line) => [
                'type' => 'paragraph',
                'content' => $line === '' ? [] : [['type' => 'text', 'text' => $line]],
            ], explode("\n", str_replace(["\r\n", "\r"], "\n", $value ?? ''))),
        ];
    }

    public static function forStorage(?string $value, string $field, ?int $maximum = null): ?string
    {
        $plain = self::plain($value);
        if ($maximum !== null && mb_strlen($plain) > $maximum) {
            throw ValidationException::withMessages([$field => 'Special requirements must not exceed '.$maximum.' characters.']);
        }
        if (trim($plain) === '') {
            return null;
        }

        $stored = str_starts_with($value ?? '', self::PREFIX)
            ? self::PREFIX.json_encode(self::document($value), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
            : $value;
        // MySQL TEXT has a byte limit; leave room for automatic provider-date guidance.
        $byteLimit = $maximum === null ? 65535 : 65000;
        if (strlen($stored) > $byteLimit) {
            throw ValidationException::withMessages([$field => 'This field contains too much text or formatting. Shorten it before saving.']);
        }

        return $stored;
    }

    public static function html(?string $value): string
    {
        return self::renderNode(self::document($value));
    }

    public static function plain(?string $value): string
    {
        if (! str_starts_with($value ?? '', self::PREFIX)) {
            return $value ?? '';
        }

        return trim(self::nodeText(self::document($value)));
    }

    public static function appendParagraph(?string $value, string $paragraph): string
    {
        if (! str_starts_with($value ?? '', self::PREFIX)) {
            return trim(($value ?? '')."\n\n".$paragraph);
        }
        $document = self::document($value);
        $document['content'][] = ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $paragraph]]];

        return self::PREFIX.json_encode($document, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private static function cleanNode(array $node, int $depth = 0): array
    {
        if ($depth > 20) {
            return ['type' => 'text', 'text' => ''];
        }
        $type = $node['type'] ?? '';
        if ($type === 'text') {
            $clean = ['type' => 'text', 'text' => is_string($node['text'] ?? null) ? $node['text'] : ''];
            if (collect($node['marks'] ?? [])->contains(fn ($mark) => is_array($mark) && ($mark['type'] ?? '') === 'bold')) {
                $clean['marks'] = [['type' => 'bold']];
            }

            return $clean;
        }
        $clean = ['type' => in_array($type, ['doc', 'paragraph', 'bulletList', 'dashList', 'listItem', 'hardBreak'], true) ? $type : 'paragraph'];
        if ($type === 'bulletList' && ($node['attrs']['listStyle'] ?? '') === 'dash') {
            $clean['attrs'] = ['listStyle' => 'dash'];
        }
        $clean['content'] = [];
        foreach (is_array($node['content'] ?? null) ? $node['content'] : [] as $child) {
            if (is_array($child)) {
                $clean['content'][] = self::cleanNode($child, $depth + 1);
            }
        }

        return $clean;
    }

    private static function renderNode(array $node): string
    {
        if ($node['type'] === 'text') {
            $text = htmlspecialchars($node['text'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return empty($node['marks']) ? $text : '<strong>'.$text.'</strong>';
        }
        $content = implode('', array_map(self::renderNode(...), $node['content'] ?? []));

        return match ($node['type']) {
            'paragraph' => '<p>'.$content.'</p>',
            'hardBreak' => '<br>',
            'bulletList' => '<ul'.(($node['attrs']['listStyle'] ?? '') === 'dash' ? ' data-list-style="dash"' : '').'>'.$content.'</ul>',
            'dashList' => '<ul data-list-style="dash">'.$content.'</ul>',
            'listItem' => '<li>'.$content.'</li>',
            default => $content,
        };
    }

    private static function nodeText(array $node): string
    {
        if ($node['type'] === 'text') {
            return $node['text'];
        }
        if ($node['type'] === 'hardBreak') {
            return "\n";
        }
        $text = implode('', array_map(self::nodeText(...), $node['content'] ?? []));

        return in_array($node['type'], ['paragraph', 'listItem'], true) ? $text."\n" : $text;
    }
}

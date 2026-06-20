<?php

use App\Services\EditorJsRenderer;

function renderBlocks(array $blocks): string
{
    return (string) (new EditorJsRenderer)->render(['blocks' => $blocks]);
}

it('builds a nested table of contents whose anchors match the headings', function () {
    $blocks = [
        ['type' => 'header', 'data' => ['text' => 'First Section', 'level' => 2]],
        ['type' => 'header', 'data' => ['text' => 'A Subsection', 'level' => 3]],
        ['type' => 'paragraph', 'data' => ['text' => 'body']],
        ['type' => 'header', 'data' => ['text' => 'Second Section', 'level' => 2]],
    ];

    $toc = (string) (new EditorJsRenderer)->tableOfContents(['blocks' => $blocks]);
    $body = renderBlocks($blocks);

    expect($toc)->toContain('href="#first-section"')
        ->and($toc)->toContain('href="#a-subsection"')
        ->and($toc)->toContain('href="#second-section"')
        ->and($toc)->toContain('First Section')
        // The H3 nests under its H2 (a sublist is opened).
        ->and(substr_count($toc, '<ul'))->toBeGreaterThanOrEqual(2)
        // Anchors line up with the rendered heading ids.
        ->and($body)->toContain('id="first-section"')
        ->and($body)->toContain('id="a-subsection"');
});

it('returns an empty table of contents when there are no headings', function () {
    $toc = (string) (new EditorJsRenderer)->tableOfContents(['blocks' => [
        ['type' => 'paragraph', 'data' => ['text' => 'no headings here']],
    ]]);

    expect($toc)->toBe('');
});

it('renders a paragraph', function () {
    $html = renderBlocks([
        ['type' => 'paragraph', 'data' => ['text' => 'Hello <b>world</b>']],
    ]);

    expect($html)->toBe('<p>Hello <b>world</b></p>');
});

it('strips disallowed inline tags but keeps formatting', function () {
    $html = renderBlocks([
        ['type' => 'paragraph', 'data' => ['text' => 'Safe <i>text</i> <script>alert(1)</script>']],
    ]);

    expect($html)->toContain('<i>text</i>')
        ->and($html)->not->toContain('<script>');
});

it('renders headers as h2/h3 with slugified anchor ids and never h1', function () {
    $html = renderBlocks([
        ['type' => 'header', 'data' => ['text' => 'My Section', 'level' => 2]],
        ['type' => 'header', 'data' => ['text' => 'Sub Section', 'level' => 3]],
        ['type' => 'header', 'data' => ['text' => 'Forced Down', 'level' => 1]],
    ]);

    expect($html)->toContain('<h2 id="my-section">My Section</h2>')
        ->and($html)->toContain('<h3 id="sub-section">Sub Section</h3>')
        ->and($html)->toContain('<h2 id="forced-down">Forced Down</h2>')
        ->and($html)->not->toContain('<h1');
});

it('renders images lazily with alt from the caption', function () {
    $html = renderBlocks([
        ['type' => 'image', 'data' => ['file' => ['url' => '/storage/a.jpg'], 'caption' => 'A cat']],
    ]);

    expect($html)->toContain('loading="lazy"')
        ->and($html)->toContain('alt="A cat"')
        ->and($html)->toContain('<figcaption>A cat</figcaption>');
});

it('falls back to media alt text when the image has no caption', function () {
    $html = renderBlocks([
        ['type' => 'image', 'data' => ['file' => ['url' => '/storage/a.jpg', 'alt' => 'Library alt']]],
    ]);

    expect($html)->toContain('alt="Library alt"')
        ->and($html)->not->toContain('<figcaption>');
});

it('renders a library image block through the image path', function () {
    $html = renderBlocks([
        ['type' => 'libraryImage', 'data' => ['file' => ['url' => '/storage/lib.jpg'], 'caption' => 'From library']],
    ]);

    expect($html)->toContain('<figure>')
        ->and($html)->toContain('src="/storage/lib.jpg"')
        ->and($html)->toContain('alt="From library"')
        ->and($html)->toContain('loading="lazy"');
});

it('renders a gallery as a lazy image grid', function () {
    $html = renderBlocks([
        ['type' => 'gallery', 'data' => [
            'files' => [
                ['url' => '/storage/1.jpg', 'alt' => 'One'],
                ['url' => '/storage/2.jpg', 'alt' => 'Two'],
            ],
            'caption' => 'Trip',
        ]],
    ]);

    expect(substr_count($html, '<img'))->toBe(2)
        ->and(substr_count($html, 'loading="lazy"'))->toBe(2)
        ->and($html)->toContain('alt="One"')
        ->and($html)->toContain('<figcaption>Trip</figcaption>');
});

it('renders a quote with a citation', function () {
    $html = renderBlocks([
        ['type' => 'quote', 'data' => ['text' => 'To be', 'caption' => 'Shakespeare']],
    ]);

    expect($html)->toContain('<blockquote>To be</blockquote>')
        ->and($html)->toContain('<figcaption>Shakespeare</figcaption>');
});

it('renders nested ordered and unordered lists (v2 shape)', function () {
    $html = renderBlocks([
        ['type' => 'list', 'data' => [
            'style' => 'unordered',
            'items' => [
                ['content' => 'Parent', 'items' => [
                    ['content' => 'Child', 'items' => []],
                ]],
            ],
        ]],
    ]);

    expect($html)->toContain('<ul><li>Parent<ul><li>Child</li></ul></li></ul>');
});

it('renders a checklist with disabled checkboxes', function () {
    $html = renderBlocks([
        ['type' => 'list', 'data' => [
            'style' => 'checklist',
            'items' => [
                ['content' => 'Done', 'meta' => ['checked' => true], 'items' => []],
                ['content' => 'Todo', 'meta' => ['checked' => false], 'items' => []],
            ],
        ]],
    ]);

    expect($html)->toContain('<input type="checkbox" disabled checked> Done')
        ->and($html)->toContain('<input type="checkbox" disabled> Todo');
});

it('escapes code blocks', function () {
    $html = renderBlocks([
        ['type' => 'code', 'data' => ['code' => '<div>hi</div>']],
    ]);

    expect($html)->toContain('<pre><code>&lt;div&gt;hi&lt;/div&gt;</code></pre>');
});

it('renders a table with optional headings', function () {
    $html = renderBlocks([
        ['type' => 'table', 'data' => [
            'withHeadings' => true,
            'content' => [['Name', 'Age'], ['Sam', '30']],
        ]],
    ]);

    expect($html)->toContain('<th>Name</th>')
        ->and($html)->toContain('<td>Sam</td>');
});

it('ignores unknown block types and empty content', function () {
    expect(renderBlocks([['type' => 'mystery', 'data' => []]]))->toBe('')
        ->and((string) (new EditorJsRenderer)->render(null))->toBe('');
});

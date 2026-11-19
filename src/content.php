<?php
function loadMenu(PDO $pdo, string $lang): array
{
    $stmt = $pdo->prepare(
        'SELECT p.page_key, pt.slug, pt.menu_label
           FROM pages p
           JOIN page_translations pt ON pt.page_id = p.id AND pt.lang_code = ?
          WHERE p.in_menu = 1
          ORDER BY p.sort_order'
    );
    $stmt->execute([$lang]);
    return $stmt->fetchAll();
}

function findPage(PDO $pdo, string $lang, string $slug): ?array
{
    $stmt = $pdo->prepare(
        'SELECT p.id, p.page_key, pt.slug, pt.title, pt.body
           FROM page_translations pt
           JOIN pages p ON p.id = pt.page_id
          WHERE pt.lang_code = ? AND pt.slug = ?'
    );
    $stmt->execute([$lang, $slug]);
    return $stmt->fetch() ?: null;
}

// Dezelfde pagina in de andere talen: ['nl' => 'over', 'en' => 'about', ...]
function loadAlternates(PDO $pdo, int $pageId): array
{
    $stmt = $pdo->prepare('SELECT lang_code, slug FROM page_translations WHERE page_id = ?');
    $stmt->execute([$pageId]);
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function loadGigs(PDO $pdo, string $lang, bool $upcoming, int $limit = 50): array
{
    $sql = 'SELECT g.gig_date, g.start_time, g.event_name, g.venue, g.city, COALESCE(gt.info, \'\') AS info
              FROM gigs g
              LEFT JOIN gig_translations gt ON gt.gig_id = g.id AND gt.lang_code = :lang
             WHERE ' . ($upcoming ? 'g.gig_date >= CURDATE() ORDER BY g.gig_date ASC'
                                  : 'g.gig_date <  CURDATE() ORDER BY g.gig_date DESC') . '
             LIMIT :lim';
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lang', $lang);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function loadMixes(PDO $pdo, int $limit = 50): array
{
    $stmt = $pdo->prepare('SELECT title, subtitle, embed_url, link_url, cover, published_at FROM mixes WHERE active = 1 ORDER BY published_at DESC LIMIT :lim');
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function loadRider(PDO $pdo): array
{
    return $pdo->query('SELECT quantity, item FROM rider_items ORDER BY sort_order, id')->fetchAll();
}

<?php
/**
 * Demo mode: fake data so the whole UI/UX can be seen and clicked through
 * without a real api.squids.co.za connection. Toggle off by setting
 * SQ_DEMO_MODE=false once you point this at the real backend.
 */

const SQ_DEMO_MODE = true;

function sq_demo_communities(): array {
    return [
        ['id' => 'c1', 'name' => 'CapeTown',   'slug' => 'capetown',   'icon_cld' => '', 'color' => '#2DD4A8', 'member_count' => 48210, 'verified' => true],
        ['id' => 'c2', 'name' => 'Braai',      'slug' => 'braai',      'icon_cld' => '', 'color' => '#FF6B4A', 'member_count' => 31900, 'verified' => false],
        ['id' => 'c3', 'name' => 'JHBTech',    'slug' => 'jhbtech',    'icon_cld' => '', 'color' => '#9B5DF0', 'member_count' => 22750, 'verified' => true],
        ['id' => 'c4', 'name' => 'Rugby',      'slug' => 'rugby',      'icon_cld' => '', 'color' => '#FFC24B', 'member_count' => 61340, 'verified' => true],
        ['id' => 'c5', 'name' => 'LoadShedding','slug' => 'loadshedding','icon_cld' => '', 'color' => '#FF5C7A', 'member_count' => 15020, 'verified' => false],
        ['id' => 'c6', 'name' => 'GamingZA',   'slug' => 'gamingza',   'icon_cld' => '', 'color' => '#2E9BF5', 'member_count' => 9840,  'verified' => false],
    ];
}

function sq_demo_posts(string $sort = 'hot', int $page = 1): array {
    $bank = [
        ['id' => 'p1', 'title' => 'Load shedding schedule finally makes sense with this app', 'body' => "Been using this for two weeks now and honestly it's saved me so much stress with planning around stage 4. Highly recommend if you're in JHB.", 'community_slug' => 'loadshedding', 'community_name' => 'LoadShedding', 'author_handle' => 'thabo_m', 'votes_up' => 342, 'votes_down' => 12, 'comment_count' => 48, 'created_at' => time() - 3600, 'images' => []],
        ['id' => 'p2', 'title' => 'Best braai spots in Cape Town this weekend?', 'body' => 'Looking for recommendations, preferably somewhere with a view. Table Mountain hikes after are a bonus.', 'community_slug' => 'capetown', 'community_name' => 'CapeTown', 'author_handle' => 'sarah_za', 'votes_up' => 156, 'votes_down' => 3, 'comment_count' => 27, 'created_at' => time() - 7200, 'images' => []],
        ['id' => 'p3', 'title' => 'Springboks squad announcement reaction thread', 'body' => 'What did everyone think of the new lineup? Some surprising omissions this time around.', 'community_slug' => 'rugby', 'community_name' => 'Rugby', 'author_handle' => 'boks4life', 'votes_up' => 891, 'votes_down' => 45, 'comment_count' => 203, 'created_at' => time() - 10800, 'images' => []],
        ['id' => 'p4', 'title' => 'Junior dev roles in Joburg — anyone hiring?', 'body' => "Recently graduated and looking to break into tech. Portfolio's ready, just need a foot in the door. Any leads appreciated!", 'community_slug' => 'jhbtech', 'community_name' => 'JHBTech', 'author_handle' => 'code_newbie', 'votes_up' => 88, 'votes_down' => 1, 'comment_count' => 34, 'created_at' => time() - 18000, 'images' => []],
        ['id' => 'p5', 'title' => 'PS5 Pro restock at Takealot right now', 'body' => 'Just grabbed one, seems to still be in stock as of posting. Go go go.', 'community_slug' => 'gamingza', 'community_name' => 'GamingZA', 'author_handle' => 'gamer_ct', 'votes_up' => 210, 'votes_down' => 8, 'comment_count' => 61, 'created_at' => time() - 25200, 'images' => []],
        ['id' => 'p6', 'title' => 'Stage 6 tonight according to the app, brace yourselves', 'body' => '', 'community_slug' => 'loadshedding', 'community_name' => 'LoadShedding', 'author_handle' => 'eskom_watch', 'votes_up' => 445, 'votes_down' => 22, 'comment_count' => 98, 'created_at' => time() - 32400, 'images' => []],
    ];
    if ($page > 1) return []; // demo has one page only
    if ($sort === 'new') usort($bank, fn($a, $b) => $b['created_at'] <=> $a['created_at']);
    if ($sort === 'top') usort($bank, fn($a, $b) => $b['votes_up'] <=> $a['votes_up']);
    return $bank;
}

function sq_demo_user(): array {
    return [
        'id' => 'u1', 'handle' => 'demo_user', 'display_name' => 'Demo User',
        'avatar_cld' => '', 'bio' => 'Just here to see how this looks.', 'role' => 'member',
        'verified' => false, 'post_count' => 12, 'friend_count' => 34, 'follower_count' => 56,
    ];
}

/**
 * Routes a signed-API-shaped call to canned demo data, so every page
 * built against sq_api_get()/sq_api_post() works with zero real backend.
 * Logged-in state is driven by a plain cookie (sq_demo_login=1), set by
 * the demo login/logout endpoints — no real auth involved.
 */
function sq_demo_api_response(string $method, string $path, ?array $body): array {
    $loggedIn = !empty($_COOKIE['sq_demo_login']);

    // GET /api/users/me
    if ($path === '/api/users/me') {
        return $loggedIn
            ? ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'user' => sq_demo_user()]]
            : ['ok' => false, 'status' => 401, 'data' => ['ok' => false, 'error' => 'Not logged in']];
    }

    // POST /api/auth/login  (demo: any email/password succeeds)
    if ($path === '/api/auth/login' && $method === 'POST') {
        setcookie('sq_demo_login', '1', ['expires' => time() + 86400 * 7, 'path' => '/', 'samesite' => 'Lax']);
        return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'redirect' => '/home']];
    }

    // POST /api/auth/logout
    if ($path === '/api/auth/logout' && $method === 'POST') {
        setcookie('sq_demo_login', '', ['expires' => time() - 3600, 'path' => '/']);
        return ['ok' => true, 'status' => 200, 'data' => ['ok' => true]];
    }

    // GET /api/feed
    if (str_starts_with($path, '/api/feed')) {
        parse_str(parse_url($path, PHP_URL_QUERY) ?? '', $qs);
        $posts = sq_demo_posts($qs['sort'] ?? 'hot', (int)($qs['page'] ?? 1));
        return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'posts' => $posts]];
    }

    // GET /api/communities/{slug}
    if (preg_match('#^/api/communities/([a-z0-9_-]+)$#i', $path, $m) && $method === 'GET') {
        foreach (sq_demo_communities() as $c) {
            if ($c['slug'] === $m[1]) {
                $c['description'] = 'A place for everything ' . $c['name'] . ' related. Be kind, stay on topic, have fun.';
                return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'community' => $c]];
            }
        }
        return ['ok' => false, 'status' => 404, 'data' => ['ok' => false, 'error' => 'Not found']];
    }

    // GET /api/users/{handle}
    if (preg_match('#^/api/users/([a-zA-Z0-9_-]+)$#', $path, $m) && $method === 'GET' && $m[1] !== 'me') {
        $u = sq_demo_user();
        $u['handle'] = $m[1];
        $u['display_name'] = ucfirst(str_replace('_', ' ', $m[1]));
        return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'user' => $u]];
    }

    // GET /api/communities (list) — keep below the two more specific rules above
    if (str_starts_with($path, '/api/communities') && $method === 'GET') {
        return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'communities' => sq_demo_communities()]];
    }

    // GET /api/users/my-communities
    if ($path === '/api/users/my-communities') {
        $comms = $loggedIn ? array_slice(sq_demo_communities(), 0, 3) : [];
        return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'communities' => $comms]];
    }

    // GET /api/search
    if (str_starts_with($path, '/api/search')) {
        parse_str(parse_url($path, PHP_URL_QUERY) ?? '', $qs);
        $q = strtolower($qs['q'] ?? '');
        $posts = array_values(array_filter(sq_demo_posts(), fn($p) => str_contains(strtolower($p['title']), $q)));
        $comms = array_values(array_filter(sq_demo_communities(), fn($c) => str_contains(strtolower($c['name']), $q)));
        return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'posts' => $posts, 'users' => [], 'communities' => $comms, 'topics' => []]];
    }

    // POST /api/posts/vote, /api/posts/bookmark, /api/posts/poll-vote, /api/feed/interaction
    if (in_array($path, ['/api/posts/vote', '/api/posts/bookmark', '/api/posts/poll-vote', '/api/feed/interaction'], true)) {
        return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'bookmarked' => true]];
    }

    // POST /api/communities/{slug}/join
    if (preg_match('#^/api/communities/[a-z0-9_-]+/join$#i', $path) && $method === 'POST') {
        return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'joined' => true]];
    }

    // GET /api/posts/{id} — single post lookup (used by /p/[id])
    if (preg_match('#^/api/posts/([a-zA-Z0-9_-]+)$#', $path, $m) && $method === 'GET') {
        $all = sq_demo_posts();
        foreach ($all as $p) {
            if ($p['id'] === $m[1]) return ['ok' => true, 'status' => 200, 'data' => ['ok' => true, 'post' => $p]];
        }
        return ['ok' => false, 'status' => 404, 'data' => ['ok' => false, 'error' => 'Not found']];
    }

    // Fallback: empty-but-ok, so unbuilt endpoints don't hard-crash a page
    return ['ok' => true, 'status' => 200, 'data' => ['ok' => true]];
}

// In app/Http/Middleware/Cors.php
return $next($request)
    ->header('Access-Control-Allow-Origin', '*')
    ->header('Access-Control-Allow-Methods', 'GET');
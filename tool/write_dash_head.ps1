$f = "C:\Users\Kurt\Desktop\planorabeta\Planora\resources\views\dashboard\index.blade.php"
$head = @'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planora — Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        /* ── Enhanced Tab Buttons ── */
        .tab-button {
            @apply px-5 py-3 rounded-xl font-semibold text-sm transition-all duration-300 flex items-center gap-2.5;
            border: 1px solid var(--line);
            background: var(--card);
            color: var(--ink-soft);
            position: relative;
            overflow: hidden;
        }
        .tab-button::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
            pointer-events: none;
        }
        .tab-button.active {
            background: linear-gradient(135deg, var(--deep-teal), #0d5a55);
            color: var(--sand);
            border-color: var(--deep-teal);
            box-shadow: 0 4px 16px rgba(11, 61, 58, 0.25);
        }
        .tab-button:hover:not(.active) {
            background: var(--sand-deep);
            color: var(--deep-teal);
            border-color: var(--line);
            transform: translateY(-1px);
        }
        .tab-button.active::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--ember);
            border-radius: 0 2px 2px 0;
        }
        .tab-content {
            display: none;
            animation: fadeInUp 0.3s ease-out;
        }
        .tab-content.active {
            display: block;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        .float-slow { animation: float 4s ease-in-out infinite; }
        .float-fast { animation: float 2.5s ease-in-out infinite; }
'@
$head | Out-File -FilePath $f -Encoding UTF8
Write-Host "Head written: $($head.Length) chars"
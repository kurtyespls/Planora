$f = "C:\Users\Kurt\Desktop\planorabeta\Planora\\resources\\views\\dashboard\\index.blade.php"
$body1 = @'
    <main class="max-w-6xl mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h1 class="font-display text-3xl font-bold text-[var(--ink)] mb-2">
                <span x-text="welcomeMessage"></span>
            </h1>
            <p class="text-[var(--ink-soft)] text-lg">
                Welcome back! Here\'s an overview of your Planora account.
            </p>
        </div>

        <!-- Stats Overview -->
        <div class="stat-overview">
            <div class="stat-item">
                <div class="label">Current Level</div>
                <div class="value" x-text="currentLevelData.name"></div>
                <div class="mt-2 flex items-center gap-2">
                    <div class="level-circle">
                        <div class="level-circle-ring"></div>
                        <div class="level-circle-inner">
                            <span x-text="currentLevelData.level"></span>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-[var(--ink-soft)]">
                        Lvl <span x-text="currentLevelData.level"></span>
                    </span>
                </div>
            </div>
            <div class="stat-item">
                <div class="label">XP Progress</div>
                <div class="value">
                    <div class="flex items-center gap-2">
                        <span x-text="currentLevelData.xp + \' / \' + currentLevelData.xp_to_next"></span>
                        <span class="text-xs text-[var(--ink-soft)] font-normal">
                            (<span x-text="Math.round((currentLevelData.xp / currentLevelData.xp_to_next) * 100) + \'%\'"></span>)
                        </span>
                    </div>
                    <div class="xp-bar-wrap mt-2">
                        <div class="xp-bar-bg" style="width: 0%;"></div>
                        <div class="xp-bar-fill" :style="'width: ' + (currentLevelData.xp / currentLevelData.xp_to_next * 100) + \'%\';"></div>
                    </div>
                </div>
            </div>
            <div class="stat-item">
                <div class="label">Your Plans</div>
                <div class="value" x-text="userPlansCount"></div>
                <div class="mt-2 text-xs text-[var(--ink-soft)]">
                    <span x-text="userPlansCount"></span> plans created
                </div>
            </div>
        </div>
'@
$body1 | Out-File -FilePath $f -Encoding UTF8 -Append
Write-Host "Body1 written: $($body1.Length) chars"
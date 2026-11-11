$f = "C:\Users\Kurt\Desktop\planorabeta\Planora\\resources\\views\\dashboard\\index.blade.php"
$body2 = @'
        <!-- Tabs -->
        <div class="flex flex-wrap gap-2 mb-6 border-b border-[var(--line)]">
            <button class="tab-button active" :class="{'active': activeTab === 'profile'}" @click="activeTab = 'profile'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Profile
            </button>
            <button class="tab-button" :class="{'active': activeTab === 'plans'}" @click="activeTab = 'plans'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                My Plans <span x-text="userPlansCount"></span>
            </button>
            <button class="tab-button" :class="{'active': activeTab === 'public'}" @click="activeTab = 'public'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2zM8 10h8M8 14h5"/></svg>
                Public Plans
            </button>
        </div>

        <!-- Profile Tab -->
        <div class="tab-content active" :class="{'active': activeTab === 'profile'}">
            <div class="bg-[var(--card)] rounded-xl border border-[var(--line)] p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-6">
                    <div class="user-avatar"></div>
                    <div class="flex-1">
                        <h2 class="font-display text-xl font-bold text-[var(--ink)]" x-text="welcomeMessage.split('—')[0].trim()"></h2>
                        <p class="text-sm text-[var(--ink-soft)]">
                            <span class="font-medium" x-text="welcomeMessage.split('—')[1].trim()"></span>
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-xs font-semibold text-[var(--ink-soft)] uppercase tracking-wider">Email</h3>
                        <p class="text-sm text-[var(--ink)]">{{ $user->email }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold text-[var(--ink-soft)] uppercase tracking-wider">Joined</h3>
                        <p class="text-sm text-[var(--ink)]">{{ $user->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
'@
$body2 | Out-File -FilePath $f -Encoding UTF8 -Append
Write-Host "Body2 written: $($body2.Length) chars"
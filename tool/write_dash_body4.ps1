$f = "C:\Users\Kurt\Desktop\planorabeta\Planora\\resources\\views\\dashboard\\index.blade.php"
$body4 = @'
        <!-- Public Plans Tab -->
        <div class="tab-content" :class="{'active': activeTab === 'public'}">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-display text-2xl font-bold text-[var(--ink)]">Other Travelers\' Plans</h2>
                <a href="/public-plans" class="text-sm font-semibold text-[var(--deep-teal)] hover:underline">View all →</a>
            </div>

            @if($otherPublicPlans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($otherPublicPlans as $plan)
                        <div class="plan-card">
                            <div class="plan-card-content p-5">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="font-semibold text-[var(--ink)] text-lg">{{ $plan->title ?: $plan->hotel_name }}</h3>
                                    @if($plan->is_public)
                                        <span class="badge-level badge-public">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2zM8 10h8M8 14h5"/></svg>
                                            Public
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-[var(--ink-soft)] mb-2">
                                    {{ number_format($plan->budget, 2) }} PHP • {{ $plan->total_days }} days
                                </p>
                                <div class="flex items-center justify-between mt-4">
                                    <span class="text-xs text-[var(--ink-soft)] font-mono">
                                        {{ $plan->created_at->diffForHumans() }}
                                    </span>
                                    <a href="/plans/{{ $plan->id }}" class="view-btn">View plan →</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <h3>No public plans yet</h3>
                    <p>Share your plans with <span class="font-semibold text-[var(--deep-teal)]">Public</span> to inspire other travelers!</p>
                </div>
            @endif
        </div>
    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dashboard', () => ({
                activeTab: 'profile',
                welcomeMessage: '{{ $user->name }} — Your Travel Dashboard',
                userPlansCount: {{ $userPlans->count() }},
                currentLevelData: {!! json_encode($currentLevelData) !!},
                nextLevelData: {!! json_encode($nextLevelData) !!},
            }));
        });
    </script>
</body>
</html>
'@
$body4 | Out-File -FilePath $f -Encoding UTF8 -Append
Write-Host "Body4 written: $($body4.Length) chars"
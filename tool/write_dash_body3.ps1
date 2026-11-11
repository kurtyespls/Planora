$f = "C:\Users\Kurt\Desktop\planorabeta\Planora\\resources\\views\\dashboard\\index.blade.php"
$body3 = @'
        <!-- Plans Tab -->
        <div class="tab-content" :class="{'active': activeTab === 'plans'}">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-display text-2xl font-bold text-[var(--ink)]">My Plans</h2>
                <a href="/my-plans" class="text-sm font-semibold text-[var(--deep-teal)] hover:underline">View all →</a>
            </div>

            @if($userPlans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($userPlans as $plan)
                        <div class="plan-card">
                            <div class="plan-card-content p-5">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="font-semibold text-[var(--ink)] text-lg">{{ $plan->title ?: $plan->hotel_name }}</h3>
                                    <span class="badge-level badge-public">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2zM8 10h8M8 14h5"/></svg>
                                        Public
                                    </span>
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
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3>No plans yet</h3>
                    <p>Create your first travel plan to start planning your next adventure!</p>
                    <a href="/plans/create" class="mt-4 inline-flex items-center px-4 py-2 bg-[var(--deep-teal)] text-[var(--sand)] rounded-lg font-semibold text-sm hover:bg-[var(--deep-teal-ink)] transition-colors focus-visible-ring">
                        Create a plan →
                    </a>
                </div>
            @endif
        </div>
'@
$body3 | Out-File -FilePath $f -Encoding UTF8 -Append
Write-Host "Body3 written: $($body3.Length) chars"
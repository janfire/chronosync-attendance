@if(session()->has('impersonated_by'))
    <div class="bg-indigo-600 text-white w-full sticky top-0 z-[100] shadow-md relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 16px 16px;"></div>
        
        <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between relative z-10">
            <div class="flex items-center gap-3">
                <div class="bg-white/20 rounded-lg p-1.5 shrink-0 animate-pulse">
                    <i class="fas fa-user-secret text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold truncate">
                        Impersonation Mode Active
                    </p>
                    <p class="text-xs text-indigo-200 truncate hidden sm:block">
                        You are currently viewing the system as <span class="text-white font-semibold">{{ app('current_tenant')->company_name ?? 'a Tenant Admin' }}</span>.
                    </p>
                </div>
            </div>
            
            <form action="{{ route('impersonation.leave') }}" method="POST" class="shrink-0">
                @csrf
                <button type="submit" class="bg-white text-indigo-700 hover:bg-indigo-50 font-bold text-xs px-4 py-1.5 rounded-full shadow-sm transition-colors whitespace-nowrap">
                    Leave Impersonation <i class="fas fa-sign-out-alt ml-1"></i>
                </button>
            </form>
        </div>
    </div>
@endif

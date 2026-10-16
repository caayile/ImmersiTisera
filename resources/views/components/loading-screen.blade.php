@props([
    'message' => 'Selamat Datang',
    'subtitle' => 'Ini halaman utama contoh. Ganti isi <main> dengan halaman Magang Dosen TSU Anda.',
])

<div 
    id="loading-screen" 
    x-data="{ 
        progress: 0, 
        loaded: false,
        init() {
            this.animateProgress();
        },
        animateProgress() {
            const duration = 2000; // 2 detik
            const steps = 100;
            const stepDuration = duration / steps;
            
            const interval = setInterval(() => {
                if (this.progress >= 100) {
                    clearInterval(interval);
                    this.loaded = true;
                    setTimeout(() => {
                        this.$el.style.opacity = '0';
                        setTimeout(() => {
                            this.$el.style.display = 'none';
                        }, 500);
                    }, 300);
                } else {
                    this.progress += 1;
                }
            }, stepDuration);
        }
    }"
    x-cloak
    {{ $attributes->merge(['class' => 'fixed inset-0 z-[9999] flex items-center justify-center bg-gradient-to-br from-teal-50 via-emerald-50 to-cyan-50']) }}
    style="transition: opacity 0.5s ease-out;"
>
    <div class="relative text-center">
        <!-- Logo atau Icon -->
        <div class="mb-8 flex justify-center">
            <div class="relative">
                <div class="absolute inset-0 animate-ping rounded-full bg-teal-400 opacity-20"></div>
                <div class="relative flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-600 shadow-2xl">
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Progress Circle -->
        <div class="mb-6 flex justify-center">
            <div class="relative h-32 w-32">
                <!-- Background Circle -->
                <svg class="h-32 w-32 -rotate-90 transform">
                    <circle 
                        cx="64" 
                        cy="64" 
                        r="56" 
                        stroke="currentColor" 
                        stroke-width="8" 
                        fill="none"
                        class="text-teal-200"
                    />
                    <!-- Progress Circle -->
                    <circle 
                        cx="64" 
                        cy="64" 
                        r="56" 
                        stroke="currentColor" 
                        stroke-width="8" 
                        fill="none"
                        class="text-teal-600 transition-all duration-300 ease-out"
                        :style="`stroke-dasharray: ${2 * Math.PI * 56}; stroke-dashoffset: ${2 * Math.PI * 56 * (1 - progress / 100)}`"
                        stroke-linecap="round"
                    />
                </svg>
                
                <!-- Percentage Text -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-3xl font-bold text-teal-700" x-text="progress + '%'">0%</span>
                </div>
            </div>
        </div>

        <!-- Message -->
        <h2 class="mb-2 text-2xl font-bold text-teal-900">{{ $message }}</h2>
        <p class="mx-auto max-w-md text-sm text-teal-700">{{ $subtitle }}</p>

        <!-- Animated Dots -->
        <div class="mt-6 flex justify-center space-x-2">
            <div class="h-2 w-2 animate-bounce rounded-full bg-teal-500" style="animation-delay: 0s"></div>
            <div class="h-2 w-2 animate-bounce rounded-full bg-teal-500" style="animation-delay: 0.2s"></div>
            <div class="h-2 w-2 animate-bounce rounded-full bg-teal-500" style="animation-delay: 0.4s"></div>
        </div>

        <!-- Replay Button (shown after loading completes) -->
        <div x-show="loaded" x-transition class="mt-6">
            <button 
                @click="progress = 0; loaded = false; animateProgress()"
                class="rounded-lg bg-teal-600 px-6 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-teal-700"
            >
                Putar ulang loading
            </button>
        </div>
    </div>
</div>

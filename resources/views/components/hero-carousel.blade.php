@props([
    'slides' => [],
    'height' => 'h-[360px] sm:h-[380px] md:h-[400px]',
    'interval' => 2000,
    'cardWidth' => 'w-[74%] sm:w-[74%]',
    'radius' => 'rounded-[16px] sm:rounded-[20px]',
    'emptyMessage' => 'Belum ada banner mitra. Admin dapat menambahkannya di Hero Unit Bisnis.',
])

@php
    $slidesData = is_array($slides) ? array_values($slides) : (method_exists($slides, 'values') ? $slides->values()->toArray() : array_values((array) $slides));
@endphp

<div
    class="hero-carousel relative w-full select-none"
    x-data="{
        slides: @js($slidesData),
        index: 0,
        timer: null,
        get count() { return this.slides.length; },
        prev() {
            if (!this.count) return;
            this.index = (this.index - 1 + this.count) % this.count;
            this.resetAuto();
        },
        next() {
            if (!this.count) return;
            this.index = (this.index + 1) % this.count;
            this.resetAuto();
        },
        go(i) {
            if (i === this.index) return;
            this.index = i;
            this.resetAuto();
        },
        offset(i) {
            if (!this.count) return 0;
            let d = i - this.index;
            if (d > this.count / 2) d -= this.count;
            if (d < -this.count / 2) d += this.count;
            return d;
        },
        start() {
            this.stop();
            if (this.count < 2) return;
            this.timer = setInterval(() => {
                this.index = (this.index + 1) % this.count;
            }, {{ $interval }});
        },
        stop() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        resetAuto() {
            this.stop();
            this.start();
        }
    }"
    x-init="start()"
    @mouseenter="stop()"
    @mouseleave="start()"
>
    <!-- Carousel Stage Container -->
    <div class="relative mx-auto w-full max-w-5xl px-8 sm:px-12 md:px-14">
        <!-- Navigation Buttons: Bulat 50%, Posisi Absolute, Hover scale(1.1) transisi 150ms -->
        <button
            type="button"
            @click="prev()"
            class="partner-nav left-0 !rounded-full transition-transform duration-150 ease-out hover:scale-110 active:scale-95 focus:outline-none"
            aria-label="Sebelumnya"
        >
            <span class="material-symbols-outlined text-[24px]">chevron_left</span>
        </button>

        <button
            type="button"
            @click="next()"
            class="partner-nav right-0 !rounded-full transition-transform duration-150 ease-out hover:scale-110 active:scale-95 focus:outline-none"
            aria-label="Berikutnya"
        >
            <span class="material-symbols-outlined text-[24px]">chevron_right</span>
        </button>

        <!-- Carousel Cards Container (Animasi: translateX & scale, durasi 450ms cubic-bezier(0.4, 0, 0.2, 1)) -->
        <div class="relative mx-auto {{ $height }} w-full overflow-visible">
            <template x-for="(slide, i) in slides" :key="slide.id || i">
                <a
                    :href="slide.url || 'javascript:void(0)'"
                    class="partner-card absolute inset-y-0 left-1/2 flex {{ $cardWidth }} items-center justify-center overflow-hidden {{ $radius }} bg-[#0c221c] shadow-2xl border border-white/10"
                    :style="`
                        transform: translateX(calc(-50% + ${offset(i) * 58}%)) scale(${offset(i) === 0 ? 1 : 0.85});
                        z-index: ${20 - Math.abs(offset(i))};
                        opacity: ${Math.abs(offset(i)) > 1 ? 0 : (offset(i) === 0 ? 1 : 0.55)};
                        pointer-events: ${offset(i) === 0 ? 'auto' : 'none'};
                        transition: transform 450ms cubic-bezier(0.4, 0, 0.2, 1), opacity 450ms cubic-bezier(0.4, 0, 0.2, 1);
                    `"
                >
                    <!-- Background Image -->
                    <template x-if="slide.image">
                        <img
                            :src="slide.image"
                            :alt="slide.title"
                            class="absolute inset-0 h-full w-full object-cover select-none"
                            loading="lazy"
                        >
                    </template>

                    <!-- Gradient Overlay -->
                    <div class="pointer-events-none absolute inset-0 z-10 bg-gradient-to-t from-black/85 via-black/25 to-transparent"></div>

                    <!-- Text Overlay (Judul + Subjudul di Bawah Card) -->
                    <div class="absolute inset-x-0 bottom-0 z-20 p-5 sm:p-7 md:p-8 text-left">
                        <h2
                            class="text-xl sm:text-2xl md:text-3xl font-semibold tracking-tight text-white drop-shadow-md"
                            x-text="slide.title"
                        ></h2>
                        <p
                            class="mt-1.5 sm:mt-2 line-clamp-2 max-w-xl text-xs sm:text-sm text-white/85 leading-relaxed drop-shadow-sm font-normal"
                            x-text="slide.subtitle"
                        ></p>
                    </div>
                </a>
            </template>

            <!-- Fallback Kosong -->
            <template x-if="!count">
                <div class="flex h-full items-center justify-center {{ $radius }} border border-white/20 bg-black/25 p-6 text-center text-sm text-white/85 backdrop-blur">
                    {{ $emptyMessage }}
                </div>
            </template>
        </div>

        <!-- Dot Indicator: Aktif pill width 20px, Inaktif bulat 6px, Height 6px, Border-radius 3px, Transisi 300ms -->
        <div class="mt-6 flex items-center justify-center gap-1.5">
            <template x-for="(slide, i) in slides" :key="'dot-' + (slide.id || i)">
                <button
                    type="button"
                    class="h-[6px] cursor-pointer focus:outline-none"
                    :style="`
                        width: ${i === index ? '20px' : '6px'};
                        border-radius: 3px;
                        background: ${i === index ? 'var(--color-secondary, #7dd8b5)' : 'rgba(255, 255, 255, 0.4)'};
                        transition: width 300ms cubic-bezier(0.4, 0, 0.2, 1), background 300ms ease;
                    `"
                    @click="go(i)"
                    :aria-label="'Pindah ke slide ' + (i + 1)"
                ></button>
            </template>
        </div>
    </div>
</div>

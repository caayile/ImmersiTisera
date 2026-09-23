@props([
    'name' => 'signature',
    'label' => 'Tanda tangan digital',
    'hint' => 'Coret di kotak atau unggah gambar tanda tangan (PNG/JPG).',
    'required' => false,
    'disabled' => false,
    'value' => null,
])

@php
    $padId = 'sig-'.preg_replace('/[^a-zA-Z0-9_]/', '-', $name).'-'.Str::random(5);
@endphp

<div
    {{ $attributes->merge(['class' => 'rounded-2xl border border-line bg-bg p-4']) }}
    x-data="{
        drawing: false,
        hasInk: {{ $value ? 'true' : 'false' }},
        dataUrl: @js($value ?: ''),
        required: {{ $required ? 'true' : 'false' }},
        disabled: {{ $disabled ? 'true' : 'false' }},
        ratio: 1,
        init() {
            if (this.disabled) return;
            this.$nextTick(() => {
                this.setupCanvas();
                if (this.dataUrl) this.paintImage(this.dataUrl);
                if (window.ResizeObserver && this.$refs.frame) {
                    new ResizeObserver(() => {
                        const snapshot = this.hasInk && this.$refs.canvas.width ? this.$refs.canvas.toDataURL('image/png') : this.dataUrl;
                        this.setupCanvas();
                        if (snapshot) this.paintImage(snapshot);
                    }).observe(this.$refs.frame);
                }
            });
        },
        setupCanvas() {
            const canvas = this.$refs.canvas;
            if (! canvas) return;
            const rect = canvas.getBoundingClientRect();
            const width = Math.max(Math.floor(rect.width), 280);
            const height = Math.max(Math.floor(rect.height) || 160, 160);
            this.ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = Math.floor(width * this.ratio);
            canvas.height = Math.floor(height * this.ratio);
            canvas.style.width = '100%';
            canvas.style.height = height + 'px';
            const ctx = canvas.getContext('2d');
            ctx.setTransform(this.ratio, 0, 0, this.ratio, 0, 0);
            ctx.lineWidth = 2.4;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#12261d';
        },
        ensureSized() {
            const canvas = this.$refs.canvas;
            if (! canvas) return;
            const rect = canvas.getBoundingClientRect();
            if (! canvas.width || rect.width < 10) {
                this.setupCanvas();
            }
        },
        point(event) {
            const rect = this.$refs.canvas.getBoundingClientRect();
            return { x: event.clientX - rect.left, y: event.clientY - rect.top };
        },
        start(event) {
            if (this.disabled) return;
            this.ensureSized();
            this.drawing = true;
            try { this.$refs.canvas.setPointerCapture(event.pointerId); } catch (e) {}
            const p = this.point(event);
            const ctx = this.$refs.canvas.getContext('2d');
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        },
        move(event) {
            if (! this.drawing || this.disabled) return;
            const p = this.point(event);
            const ctx = this.$refs.canvas.getContext('2d');
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            this.hasInk = true;
        },
        end() {
            if (! this.drawing) return;
            this.drawing = false;
            if (this.hasInk) {
                this.dataUrl = this.$refs.canvas.toDataURL('image/png');
                this.syncInput();
            }
        },
        clear() {
            if (this.disabled) return;
            this.ensureSized();
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            this.hasInk = false;
            this.dataUrl = '';
            this.syncInput();
        },
        upload(event) {
            if (this.disabled) return;
            const file = event.target.files && event.target.files[0];
            if (! file) return;
            if (! /^image\/(png|jpeg|jpg|webp)$/i.test(file.type)) {
                alert('Unggah gambar PNG, JPG, atau WEBP.');
                event.target.value = '';
                return;
            }
            if (file.size > 800000) {
                alert('Ukuran gambar maksimal 800 KB.');
                event.target.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = () => {
                this.ensureSized();
                this.paintImage(reader.result);
                this.dataUrl = reader.result;
                this.hasInk = true;
                this.syncInput();
            };
            reader.readAsDataURL(file);
            event.target.value = '';
        },
        paintImage(src) {
            if (! this.$refs.canvas || ! src) return;
            const img = new Image();
            img.onload = () => {
                this.ensureSized();
                const canvas = this.$refs.canvas;
                const ctx = canvas.getContext('2d');
                const rect = canvas.getBoundingClientRect();
                const wCss = Math.max(rect.width, 280);
                const hCss = Math.max(rect.height, 160);
                ctx.clearRect(0, 0, wCss, hCss);
                const scale = Math.min(wCss / img.width, hCss / img.height, 1);
                const w = img.width * scale;
                const h = img.height * scale;
                ctx.drawImage(img, (wCss - w) / 2, (hCss - h) / 2, w, h);
                this.hasInk = true;
            };
            img.src = src;
        },
        syncInput() {
            if (! this.$refs.input) return;
            this.$refs.input.value = this.dataUrl;
            this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
            this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }"
>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-sm font-medium">{{ $label }}@if($required)*@endif</p>
            <p class="mt-0.5 text-xs text-muted">{{ $hint }}</p>
        </div>
        @unless($disabled)
            <div class="flex flex-wrap gap-2">
                <label class="cursor-pointer rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-muted transition hover:text-ink">
                    Unggah gambar
                    <input type="file" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden" @change="upload($event)">
                </label>
                <button type="button" @click="clear()" class="rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-semibold text-muted transition hover:text-ink">
                    Hapus
                </button>
            </div>
        @endunless
    </div>

    <div class="mt-3 overflow-hidden rounded-xl border border-dashed border-line bg-white" x-ref="frame">
        @if($disabled && $value)
            <img src="{{ $value }}" alt="Tanda tangan" class="mx-auto h-40 w-full object-contain p-3">
        @else
            <div class="relative min-h-[160px]">
                <p x-show="! hasInk" class="pointer-events-none absolute inset-0 flex items-center justify-center px-4 text-center text-xs text-muted/70">
                    Coret tanda tangan di sini, atau unggah gambar
                </p>
                <canvas
                    id="{{ $padId }}"
                    x-ref="canvas"
                    class="relative block h-40 w-full touch-none {{ $disabled ? 'cursor-not-allowed opacity-60' : 'cursor-crosshair' }}"
                    @pointerdown.prevent="start($event)"
                    @pointermove.prevent="move($event)"
                    @pointerup.prevent="end()"
                    @pointercancel.prevent="end()"
                    @pointerleave="end()"
                ></canvas>
            </div>
        @endif
    </div>

    <input
        x-ref="input"
        type="hidden"
        name="{{ $name }}"
        :value="dataUrl"
        value="{{ $value }}"
        @if($required && ! $disabled) required @endif
    >
</div>

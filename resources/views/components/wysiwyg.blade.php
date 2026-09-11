@php($editorId = 'wysiwyg-' . preg_replace('/[^a-zA-Z0-9_]/', '-', $name) . '-' . Str::random(5))

<div class="overflow-hidden rounded-xl border border-line bg-white transition focus-within:border-primary"
     data-wysiwyg="{{ $editorId }}">
    <div class="flex flex-wrap items-center gap-1 border-b border-line bg-bg/60 px-2 py-1.5">
        <button type="button" data-wysiwyg-cmd="bold" title="Tebal"
                class="min-w-7 rounded-md px-1.5 py-1 text-sm font-bold text-muted transition hover:bg-white hover:text-ink">B</button>
        <button type="button" data-wysiwyg-cmd="italic" title="Miring"
                class="min-w-7 rounded-md px-1.5 py-1 text-sm italic text-muted transition hover:bg-white hover:text-ink">I</button>
        <button type="button" data-wysiwyg-cmd="underline" title="Garis bawah"
                class="min-w-7 rounded-md px-1.5 py-1 text-sm text-muted underline transition hover:bg-white hover:text-ink">U</button>

        <span class="mx-1 h-4 w-px bg-line"></span>

        <button type="button" data-wysiwyg-cmd="formatBlock" data-wysiwyg-value="h2" title="Subjudul"
                class="min-w-9 rounded-md px-1.5 py-1 text-[11px] font-bold uppercase leading-none text-muted transition hover:bg-white hover:text-ink">H2</button>
        <button type="button" data-wysiwyg-cmd="formatBlock" data-wysiwyg-value="h3" title="Subjudul kecil"
                class="min-w-9 rounded-md px-1.5 py-1 text-[11px] font-semibold uppercase leading-none text-muted transition hover:bg-white hover:text-ink">H3</button>
        <button type="button" data-wysiwyg-cmd="formatBlock" data-wysiwyg-value="p" title="Paragraf"
                class="min-w-7 rounded-md px-1.5 py-1 text-sm text-muted transition hover:bg-white hover:text-ink">¶</button>

        <span class="mx-1 h-4 w-px bg-line"></span>

        <button type="button" data-wysiwyg-cmd="insertUnorderedList" title="Daftar poin"
                class="rounded-md px-1.5 py-1 text-sm text-muted transition hover:bg-white hover:text-ink">•&nbsp;List</button>
        <button type="button" data-wysiwyg-cmd="insertOrderedList" title="Daftar angka"
                class="rounded-md px-1.5 py-1 text-sm text-muted transition hover:bg-white hover:text-ink">1.&nbsp;List</button>
        <button type="button" data-wysiwyg-cmd="formatBlock" data-wysiwyg-value="blockquote" title="Kutipan"
                class="rounded-md px-1.5 py-1 text-sm text-muted transition hover:bg-white hover:text-ink">❝&nbsp;Kutipan</button>

        <span class="mx-1 h-4 w-px bg-line"></span>

        <button type="button" data-wysiwyg-cmd="link" title="Tautan"
                class="rounded-md px-1.5 py-1 text-sm text-muted transition hover:bg-white hover:text-ink">Link</button>
        <button type="button" data-wysiwyg-cmd="removeFormat" title="Hapus format"
                class="rounded-md px-1.5 py-1 text-sm text-muted transition hover:bg-white hover:text-ink">↺</button>
    </div>

    <div class="wysiwyg-body min-h-[160px] px-4 py-3 text-sm leading-7 text-ink focus:outline-none"
         data-wysiwyg-body contenteditable="true" data-placeholder="{{ $placeholder ?? 'Tulis materi di sini...' }}">{!! $value ?? '' !!}</div>

    <textarea name="{{ $name }}" class="hidden" data-wysiwyg-output>{{ $value ?? '' }}</textarea>
</div>

<script>
(() => {
    const root = document.querySelector('[data-wysiwyg="{{ $editorId }}"]');
    if (!root) return;
    const body = root.querySelector('[data-wysiwyg-body]');
    const output = root.querySelector('[data-wysiwyg-output]');
    const sync = () => { output.value = body.innerHTML; };
    const isReallyEmpty = () =>
        body.textContent.trim() === '' && !body.querySelector('img, a, ul, ol, h2, h3, blockquote');
    const updateEmpty = () => {
        body.classList.toggle('is-empty', isReallyEmpty());
        if (isReallyEmpty() && !body.querySelector('p')) body.innerHTML = '<p><br></p>';
    };

    if (!body.querySelector('p, h2, h3, ul, ol, blockquote') && body.textContent.trim() === '') {
        body.innerHTML = '<p><br></p>';
    }
    updateEmpty();

    body.addEventListener('input', () => { sync(); updateEmpty(); });

    root.querySelectorAll('[data-wysiwyg-cmd]').forEach((btn) => {
        btn.addEventListener('mousedown', (e) => e.preventDefault());
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            body.focus();
            const cmd = btn.getAttribute('data-wysiwyg-cmd');
            const value = btn.getAttribute('data-wysiwyg-value') || false;
            if (cmd === 'link') {
                const url = window.prompt('Alamat tautan (mis. https://...):');
                if (url) document.execCommand('createLink', false, url);
            } else if (cmd === 'formatBlock') {
                document.execCommand('formatBlock', false, `<${value}>`);
            } else {
                document.execCommand(cmd, false, value);
            }
            sync();
            updateEmpty();
            btn.blur();
        });
    });
})();
</script>
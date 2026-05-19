@php
    $assistantSuggestions = match (auth()->user()?->role) {
        'super_admin', 'operational_admin', 'supervisor' => [
            'Berapa MTTR saat ini?',
            'Berapa ticket overdue saat ini?',
            'Status ticket TCK-...',
        ],
        'engineer' => [
            'Task saya',
            'Status ticket TCK-...',
            'Modul apa yang bisa saya akses?',
        ],
        'inspection_officer' => [
            'Inspeksi saya',
            'Status ticket TCK-...',
            'Modul apa yang bisa saya akses?',
        ],
        default => [
            'Tiket saya',
            'Status ticket TCK-...',
            'Modul apa yang bisa saya akses?',
        ],
    };
@endphp

<div
    id="cxts-floating-assistant"
    class="cxts-assistant"
    data-endpoint="{{ route('assistant.respond') }}"
    data-role="{{ auth()->user()?->role }}"
    data-suggestions='@json($assistantSuggestions)'
>
    <button type="button" class="cxts-assistant__fab" data-assistant-toggle aria-label="Open CXTS assistant">
        <iconify-icon icon="solar:chat-round-dots-outline"></iconify-icon>
        <span>Assistant</span>
    </button>

    <div class="cxts-assistant__panel" hidden>
        <div class="cxts-assistant__header">
            <div>
                <div class="cxts-assistant__title">CXTS Assistant</div>
                <div class="cxts-assistant__subtitle">Read-only, sesuai role, hanya lingkup aplikasi</div>
            </div>
            <button type="button" class="cxts-assistant__close" data-assistant-close aria-label="Close assistant">
                <iconify-icon icon="solar:close-circle-outline"></iconify-icon>
            </button>
        </div>

        <div class="cxts-assistant__messages" data-assistant-messages>
            <div class="cxts-assistant__message cxts-assistant__message--assistant">
                Saya bisa bantu jawab hal terkait CXTS dan data yang memang boleh Anda akses. Coba salah satu contoh pertanyaan di bawah.
            </div>
        </div>

        <div class="cxts-assistant__suggestions" data-assistant-suggestions>
            @foreach ($assistantSuggestions as $suggestion)
                <button type="button" class="cxts-assistant__chip" data-assistant-suggestion="{{ $suggestion }}">{{ $suggestion }}</button>
            @endforeach
        </div>

        <form class="cxts-assistant__composer" data-assistant-form>
            <textarea
                class="cxts-assistant__input"
                name="message"
                rows="2"
                maxlength="1000"
                placeholder="Tanya tentang ticket, task, inspection, MTTR, atau modul CXTS..."
                required
            ></textarea>
            <button type="submit" class="btn btn-primary cxts-assistant__send">Kirim</button>
        </form>
    </div>
</div>

<style>
    .cxts-assistant {
        position: fixed;
        right: 1.25rem;
        bottom: 1.25rem;
        z-index: 1060;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 0.75rem;
    }

    .cxts-assistant__fab {
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, #0f766e, #2563eb);
        color: #fff;
        min-height: 56px;
        padding: 0.9rem 1rem;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        font-weight: 600;
    }

    .cxts-assistant__fab iconify-icon {
        font-size: 1.2rem;
    }

    .cxts-assistant__panel {
        width: min(380px, calc(100vw - 1.5rem));
        max-height: min(640px, calc(100vh - 7rem));
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(148, 163, 184, 0.2);
        border-radius: 1.2rem;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        overflow: hidden;
        backdrop-filter: blur(14px);
        display: flex;
        flex-direction: column;
    }

    .cxts-assistant__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1rem 0.85rem;
        background: linear-gradient(180deg, rgba(37, 99, 235, 0.08), rgba(15, 118, 110, 0.02));
        border-bottom: 1px solid rgba(148, 163, 184, 0.16);
    }

    .cxts-assistant__title {
        font-weight: 700;
        color: #0f172a;
    }

    .cxts-assistant__subtitle {
        color: #64748b;
        font-size: 0.8rem;
        margin-top: 0.2rem;
    }

    .cxts-assistant__close {
        border: 0;
        background: transparent;
        color: #64748b;
        padding: 0;
        line-height: 1;
    }

    .cxts-assistant__close iconify-icon {
        font-size: 1.2rem;
    }

    .cxts-assistant__messages {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.9), rgba(255, 255, 255, 1));
    }

    .cxts-assistant__message {
        max-width: 92%;
        padding: 0.85rem 0.95rem;
        border-radius: 1rem;
        white-space: pre-wrap;
        font-size: 0.92rem;
        line-height: 1.45;
    }

    .cxts-assistant__message--assistant {
        align-self: flex-start;
        background: #eef6ff;
        color: #0f172a;
        border-top-left-radius: 0.45rem;
    }

    .cxts-assistant__message--user {
        align-self: flex-end;
        background: #1d4ed8;
        color: #fff;
        border-top-right-radius: 0.45rem;
    }

    .cxts-assistant__message--status {
        align-self: center;
        background: rgba(148, 163, 184, 0.12);
        color: #475569;
        font-size: 0.8rem;
        padding: 0.45rem 0.7rem;
    }

    .cxts-assistant__message--typing {
        align-self: flex-start;
        background: #eef6ff;
        color: #0f172a;
        border-top-left-radius: 0.45rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        min-width: 72px;
    }

    .cxts-assistant__typing-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #2563eb;
        opacity: 0.25;
        animation: cxtsAssistantTyping 1.15s infinite ease-in-out;
    }

    .cxts-assistant__typing-dot:nth-child(2) {
        animation-delay: 0.18s;
    }

    .cxts-assistant__typing-dot:nth-child(3) {
        animation-delay: 0.36s;
    }

    @keyframes cxtsAssistantTyping {
        0%, 80%, 100% {
            transform: translateY(0);
            opacity: 0.25;
        }

        40% {
            transform: translateY(-4px);
            opacity: 1;
        }
    }

    .cxts-assistant__suggestions {
        display: flex;
        gap: 0.55rem;
        flex-wrap: wrap;
        padding: 0 1rem 1rem;
        max-height: 140px;
        overflow-y: auto;
        flex: 0 0 auto;
    }

    .cxts-assistant__chip {
        border: 1px solid rgba(37, 99, 235, 0.16);
        background: #fff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 0.45rem 0.7rem;
        font-size: 0.8rem;
    }

    .cxts-assistant__composer {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 1rem;
        border-top: 1px solid rgba(148, 163, 184, 0.16);
        background: #fff;
        flex: 0 0 auto;
    }

    .cxts-assistant__input {
        width: 100%;
        resize: vertical;
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 0.95rem;
        padding: 0.8rem 0.9rem;
        min-height: 74px;
        outline: none;
    }

    .cxts-assistant__input:focus {
        border-color: rgba(37, 99, 235, 0.38);
        box-shadow: 0 0 0 0.24rem rgba(37, 99, 235, 0.1);
    }

    .cxts-assistant__send {
        align-self: flex-end;
        position: relative;
        z-index: 1;
        min-width: 88px;
    }

    @media (max-width: 576px) {
        .cxts-assistant {
            right: 0.75rem;
            left: 0.75rem;
            bottom: 0.75rem;
            align-items: stretch;
        }

        .cxts-assistant__fab {
            justify-content: center;
        }

        .cxts-assistant__panel {
            width: 100%;
        }
    }
</style>

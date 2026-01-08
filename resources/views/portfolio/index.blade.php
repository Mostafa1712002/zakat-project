@extends('layouts.app')

@section('title', 'معرض الأعمال')

@section('content')
<div class="page-header">
    <div>
        <h1>📸 معرض الأعمال</h1>
        <p>استعرض جميع صفحات النظام بجودة عالية</p>
    </div>
    <div class="header-actions">
        @if($hasVideo)
        <a href="{{ asset($videoPath) }}" class="btn btn-primary" download>
            🎬 تحميل الفيديو
        </a>
        @endif
        <a href="{{ asset('gallery/PORTFOLIO.md') }}" class="btn btn-secondary" target="_blank">
            📄 الدليل الكامل
        </a>
    </div>
</div>

@if($hasVideo)
<div style="background: white; border-radius: 12px; padding: 24px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <h2 style="font-size: 20px; font-weight: 700; margin-bottom: 16px;">🎬 جولة فيديو في النظام</h2>
    <p style="color: #64748b; margin-bottom: 16px;">شاهد جولة كاملة في جميع صفحات نظام CRM (~70 ثانية)</p>
    <video controls style="width: 100%; border-radius: 8px; max-height: 600px;">
        <source src="{{ asset($videoPath) }}" type="video/webm">
        متصفحك لا يدعم تشغيل الفيديو
    </video>
</div>
@endif

<div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-size: 20px; font-weight: 700;">📸 معرض الصور ({{ count($screenshots) }} صورة)</h2>

        <div style="display: flex; gap: 8px;">
            @foreach($viewports as $size => $name)
            <button
                onclick="filterBySize('{{ $size }}')"
                class="filter-btn"
                style="padding: 8px 16px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer; font-family: inherit; font-size: 14px; transition: all 0.2s;"
                data-size="{{ $size }}">
                {{ $name }}
            </button>
            @endforeach
            <button
                onclick="filterBySize('all')"
                class="filter-btn active"
                style="padding: 8px 16px; background: #0891b2; color: white; border: none; border-radius: 6px; cursor: pointer; font-family: inherit; font-size: 14px; transition: all 0.2s;">
                الكل
            </button>
        </div>
    </div>

    <div id="gallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @foreach($screenshots as $screenshot)
        <div class="screenshot-item" data-size="{{ $screenshot['size'] }}" style="cursor: pointer; transition: transform 0.2s;" onclick="openModal('{{ asset($screenshot['path']) }}', '{{ $screenshot['filename'] }}')">
            <div style="border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: box-shadow 0.2s;">
                <img
                    src="{{ asset($screenshot['path']) }}"
                    alt="{{ $screenshot['name'] }}"
                    style="width: 100%; height: auto; display: block;">
            </div>
            <div style="margin-top: 8px; padding: 0 4px;">
                <p style="font-size: 14px; font-weight: 600; color: #1e293b;">{{ str_replace('-', ' ', $screenshot['name']) }}</p>
                <p style="font-size: 12px; color: #64748b;">{{ $screenshot['size'] }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal -->
<div id="imageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; padding: 20px; overflow: auto;" onclick="closeModal()">
    <div style="max-width: 1400px; margin: auto; position: relative;">
        <button onclick="closeModal()" style="position: fixed; top: 20px; left: 20px; background: white; border: none; border-radius: 50%; width: 40px; height: 40px; font-size: 24px; cursor: pointer; z-index: 10000;">×</button>
        <img id="modalImage" src="" alt="" style="width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);">
        <p id="modalCaption" style="text-align: center; color: white; margin-top: 16px; font-size: 16px;"></p>
    </div>
</div>

<style>
.filter-btn:hover {
    opacity: 0.8;
}
.filter-btn.active {
    background: #0891b2 !important;
    color: white !important;
}
.screenshot-item:hover {
    transform: translateY(-4px);
}
.screenshot-item:hover > div {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>

<script>
function filterBySize(size) {
    const items = document.querySelectorAll('.screenshot-item');
    const buttons = document.querySelectorAll('.filter-btn');

    // Update button states
    buttons.forEach(btn => {
        if (size === 'all' && btn.textContent.trim() === 'الكل') {
            btn.classList.add('active');
        } else if (btn.dataset.size === size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Filter items
    items.forEach(item => {
        if (size === 'all' || item.dataset.size === size) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function openModal(imageSrc, caption) {
    document.getElementById('imageModal').style.display = 'block';
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('modalCaption').textContent = caption;
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('imageModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection

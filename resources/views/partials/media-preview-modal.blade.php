<!-- Universal Media Lightbox Modal -->
<div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-labelledby="mediaPreviewModalTitle" aria-hidden="true" style="z-index: 2100;">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: min(860px, 94vw);">
        <div class="modal-content border-0 shadow-2xl overflow-hidden" style="background: #090d16; color: #fff; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);">
            <div class="modal-header border-0 py-3 px-4 d-flex align-items-center justify-content-between" style="background: rgba(255, 255, 255, 0.04); border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-3">
                    <span class="badge rounded-pill" id="mediaPreviewBadge" style="background: rgba(139, 92, 246, 0.25); color: #c4b5fd; font-size: 0.72rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em; padding: 5px 11px;">Preview</span>
                    <h5 class="modal-title fs-6 fw-bold text-truncate text-white mb-0" id="mediaPreviewModalTitle">Media Preview</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 d-flex align-items-center justify-content-center" style="min-height: 360px; max-height: 76vh; background: #020617; position: relative;">
                <div id="mediaPreviewBody" class="w-100 h-100 d-flex align-items-center justify-content-center p-3">
                    <!-- Media element inserted dynamically via JavaScript -->
                </div>
            </div>
            <div class="modal-footer border-0 py-2.5 px-4 d-flex justify-content-between align-items-center" style="background: rgba(255, 255, 255, 0.04); border-top: 1px solid rgba(255, 255, 255, 0.08) !important;">
                <span class="text-white-50 small text-truncate" id="mediaPreviewMeta" style="max-width: 60%;"></span>
                <div class="d-flex gap-2">
                    <a href="#" id="mediaPreviewOpenBtn" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-light rounded-pill px-3" style="font-size: 0.8rem;">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open Original
                    </a>
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal" style="font-size: 0.8rem;">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[data-media-popup] {
    cursor: pointer !important;
    transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), filter 0.18s ease;
}
[data-media-popup]:hover {
    transform: scale(1.04);
    filter: brightness(1.06);
}
.media-preview-play-badge {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.82);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    pointer-events: none;
    transition: transform 0.18s ease, background 0.18s ease;
}
[data-media-popup]:hover .media-preview-play-badge {
    transform: translate(-50%, -50%) scale(1.15);
    background: rgba(124, 58, 237, 0.9);
}
</style>

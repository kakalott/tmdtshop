<div id="chat-widget" class="chat-widget" aria-live="polite">
    <button type="button" id="chat-toggle" class="chat-widget__toggle" aria-expanded="false" aria-controls="chat-panel" title="Hỗ trợ trực tuyến">
        <span class="chat-widget__toggle-icon" aria-hidden="true">💬</span>
        <span class="chat-widget__toggle-label">Hỗ trợ</span>
    </button>

    <div id="chat-panel" class="chat-widget__panel" hidden>
        <header class="chat-widget__header">
            <strong>Trợ lý {{ config('chatbot.shop_name') }}</strong>
            <button type="button" id="chat-close" class="btn-close btn-close-white" aria-label="Đóng"></button>
        </header>

        <div id="chat-messages" class="chat-widget__messages" role="log"></div>

        <form id="chat-form" class="chat-widget__form">
            <textarea
                id="chat-input"
                class="form-control form-control-sm"
                rows="2"
                maxlength="2000"
                placeholder="Nhập câu hỏi (vd: đơn #5, sản phẩm còn hàng...)"
                required
            ></textarea>
            <button type="submit" id="chat-send" class="btn btn-primary btn-sm w-100 mt-2">
                Gửi
            </button>
        </form>
    </div>
</div>

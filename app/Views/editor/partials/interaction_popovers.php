    <div id="aaInteractionPopover" class="aa-interaction-popover" aria-live="polite">
        <div id="aaLinkPopoverSection" class="aa-interaction-popover-section">
            <p class="aa-interaction-popover-title"><i class="fa fa-link"></i><span>Link Text</span></p>
            <label>
                Link tujuan
                <input id="aaLinkPopoverUrlInput" type="url" placeholder="https://maps.google.com/...">
            </label>
        </div>
        <div id="aaSocialPopoverSection" class="aa-interaction-popover-section is-compact">
            <p class="aa-interaction-popover-title"><i class="fa fa-share-nodes"></i><span>Social Media</span></p>
            <label>
                Icon
                <select id="aaSocialPopoverPlatformInput">
                    <option value="instagram">Instagram</option>
                    <option value="tiktok">TikTok</option>
                    <option value="youtube">YouTube</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="facebook">Facebook</option>
                    <option value="x">X</option>
                    <option value="threads">Threads</option>
                    <option value="telegram">Telegram</option>
                    <option value="pinterest">Pinterest</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="spotify">Spotify</option>
                    <option value="shopee">Shopee</option>
                    <option value="tokopedia">Tokopedia</option>
                    <option value="website">Website</option>
                </select>
            </label>
            <label>
                Nama tampil
                <input id="aaSocialPopoverLabelInput" type="text" maxlength="70" placeholder="Instagram">
            </label>
            <label class="aa-popover-full">
                Link tujuan
                <input id="aaSocialPopoverUrlInput" type="url" placeholder="https://instagram.com/...">
            </label>
        </div>
        <div id="aaCopyPopoverSection" class="aa-interaction-popover-section">
            <p class="aa-interaction-popover-title"><i class="fa fa-copy"></i><span>Copy Text</span></p>
            <label>
                Teks yang dicopy
                <textarea id="aaCopyPopoverTextInput" rows="3"
                    placeholder="Nomor rekening, alamat, kode voucher, atau teks lain"></textarea>
            </label>
            <label>
                Pesan setelah dicopy
                <input id="aaCopyPopoverFeedbackInput" type="text" placeholder="Tersalin">
            </label>
        </div>
        <div id="aaMusicPopoverSection" class="aa-interaction-popover-section is-compact">
            <p class="aa-interaction-popover-title"><i class="fa fa-music"></i><span>Music Player</span></p>
            <label class="aa-popover-full">
                Audio URL
                <input id="aaMusicPopoverUrlInput" type="url" placeholder="https://.../musik.mp3">
            </label>
            <label>
                Background
                <input id="aaMusicPopoverBgInput" type="color" value="#0f766e">
            </label>
            <label>
                Border radius
                <span class="aa-popover-range">
                    <input id="aaMusicPopoverRadiusInput" type="range" min="0" max="160" step="1" value="66">
                    <output id="aaMusicPopoverRadiusValue">66</output>
                </span>
            </label>
            <label class="aa-interaction-check">
                <input id="aaMusicPopoverAutoplayInput" type="checkbox">
                Autoplay setelah interaksi
            </label>
            <label class="aa-interaction-check">
                <input id="aaMusicPopoverLoopInput" type="checkbox">
                Loop audio
            </label>
            <label class="aa-interaction-check">
                <input id="aaMusicPopoverShowButtonInput" type="checkbox">
                Tampilkan tombol player
            </label>
        </div>
        <div id="aaYoutubePopoverSection" class="aa-interaction-popover-section">
            <p class="aa-interaction-popover-title"><i class="fa-brands fa-youtube"></i><span>Youtube Video</span></p>
            <label class="aa-popover-full">
                Link Youtube
                <input id="aaYoutubePopoverUrlInput" type="url" placeholder="https://youtu.be/...">
            </label>
            <label>
                Background
                <input id="aaYoutubePopoverBgInput" type="color" value="#111827">
            </label>
            <label>
                Border radius
                <span class="aa-popover-range">
                    <input id="aaYoutubePopoverRadiusInput" type="range" min="0" max="120" step="1" value="18">
                    <output id="aaYoutubePopoverRadiusValue">18</output>
                </span>
            </label>
            <label class="aa-interaction-check">
                <input id="aaYoutubePopoverAutoplayInput" type="checkbox">
                Autoplay saat terlihat
            </label>
            <label class="aa-interaction-check">
                <input id="aaYoutubePopoverLoopInput" type="checkbox">
                Loop video
            </label>
        </div>
        <div id="aaOpeningButtonPopoverSection" class="aa-interaction-popover-section is-compact">
            <p class="aa-interaction-popover-title"><i class="fa fa-hand-pointer"></i><span>Button Opening</span></p>
            <label>
                Background
                <input id="aaOpeningButtonBgInput" type="color" value="#0f766e">
            </label>
            <label>
                Warna teks
                <input id="aaOpeningButtonTextColorInput" type="color" value="#ffffff">
            </label>
            <label class="aa-popover-full">
                Font
                <select id="aaOpeningButtonFontInput"></select>
            </label>
            <label>
                Border radius
                <span class="aa-popover-range">
                    <input id="aaOpeningButtonRadiusInput" type="range" min="0" max="160" step="1" value="48">
                    <output id="aaOpeningButtonRadiusValue">48</output>
                </span>
            </label>
            <label>
                Padding
                <span class="aa-popover-range">
                <input id="aaOpeningButtonPaddingYInput" type="range" min="6" max="90" step="1" value="28">
                    <output id="aaOpeningButtonPaddingYValue">28</output>
                </span>
            </label>
        </div>
        <div id="aaGuestFieldPopoverSection" class="aa-interaction-popover-section is-compact">
            <p class="aa-interaction-popover-title"><i class="fa fa-message"></i><span>Guestbook Field</span></p>
            <label class="aa-popover-full">
                Teks / label
                <input id="aaGuestFieldPopoverTextInput" type="text" placeholder="Nama field">
            </label>
            <label>
                Background
                <input id="aaGuestFieldPopoverBgInput" type="color" value="#ffffff">
            </label>
            <label>
                Font
                <select id="aaGuestFieldPopoverFontInput"></select>
            </label>
            <label>
                Ukuran teks
                <input id="aaGuestFieldPopoverSizeInput" type="number" min="8" max="260" step="1" value="36">
            </label>
            <label>
                Warna teks
                <input id="aaGuestFieldPopoverColorInput" type="color" value="#334155">
            </label>
            <label>
                Border radius
                <span class="aa-popover-range">
                    <input id="aaGuestFieldPopoverRadiusInput" type="range" min="0" max="120" step="1" value="18">
                    <output id="aaGuestFieldPopoverRadiusValue">18</output>
                </span>
            </label>
            <label id="aaGuestFieldPopoverRequiredWrap" class="aa-interaction-check">
                <input id="aaGuestFieldPopoverRequiredInput" type="checkbox">
                Wajib diisi
            </label>
            <label id="aaGuestFieldPopoverMaxWrap">
                Maksimal karakter
                <input id="aaGuestFieldPopoverMaxInput" type="number" min="0" max="1000" step="1" value="0">
            </label>
        </div>
    </div>

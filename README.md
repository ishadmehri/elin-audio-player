# Elin Audio Player

ویجت اختصاصی **المنتور** برای پخش پادکست و فایل صوتی، با نمایش موج صوتی (waveform) و کنترل سرعت پخش. طراحی RTL و ریسپانسیو.

A custom Elementor widget for podcast/audio playback with a WaveSurfer waveform, skip controls and a playback-speed slider. RTL-first, responsive.

## ویژگی‌ها

- نمایش موج صوتی با [WaveSurfer.js](https://wavesurfer.xyz/) نسخه ۷ (کلیک روی موج = پرش به همان نقطه)
- دکمه پخش/توقف با آیکون داینامیک
- جلو و عقب رفتن با مقدار قابل تنظیم (پیش‌فرض ۱۵ ثانیه)
- نمایش زمان جاری و مدت کل
- اسلایدر سرعت پخش با پله‌های `0.5 / 0.75 / 1 / 1.25 / 1.5`
- رابط کاربری جداگانه برای موبایل (popup تمام‌صفحه) و دسکتاپ
- آیکون قابل انتخاب (کتابخانه آیکون یا SVG دلخواه) با کنترل رنگ پس‌زمینه، اندازه، فاصله داخلی و گردی گوشه
- تب Style کامل در المنتور: رنگ‌ها، تایپوگرافی، padding و گردی گوشه‌ها
- پشتیبانی از چند پلیر در یک صفحه
- سازگار با ویرایشگر زنده المنتور (live preview)
- قابل ترجمه (text domain: `elin-audio-player`)

## نصب

پوشه افزونه را در `wp-content/plugins/` قرار دهید و از پیشخوان وردپرس فعالش کنید.

```bash
cd wp-content/plugins
git clone https://github.com/<user>/elin-audio-player.git
```

سپس در المنتور، ویجت **Elin Audio Player** را از دسته General به صفحه بکشید و یک فایل صوتی انتخاب کنید.

## نیازمندی‌ها

| مورد | حداقل نسخه |
|---|---|
| WordPress | 5.9 |
| PHP | 7.4 |
| Elementor | 3.5.0 |

اگر المنتور نصب یا فعال نباشد، افزونه به‌جای خطای فتال یک اعلان در پیشخوان نشان می‌دهد.

## ساختار

```
elin-audio-player.php              بوت‌استرپ، بررسی نیازمندی‌ها، ثبت asset ها
widgets/elin-audio-player-widget.php   کلاس ویجت المنتور (کنترل‌ها + رندر)
assets/js/player.js                منطق پلیر روی WaveSurfer
assets/js/wavesurfer.min.js        کتابخانه WaveSurfer 7.8.4
assets/css/player.css              استایل RTL + ریسپانسیو (بر پایه CSS variables)
```

رنگ‌ها به‌صورت CSS custom property روی `.elin-player` تعریف شده‌اند (`--elin-accent`، `--elin-bg`، …) و کنترل‌های تب Style همان متغیرها را بازنویسی می‌کنند.

## توسعه

بعد از تغییر فایل‌های CSS/JS، ثابت `ELIN_AUDIO_VERSION` را در فایل اصلی بالا ببرید تا کش مرورگر پاک شود.

## لایسنس

GPL-2.0-or-later — همسو با لایسنس وردپرس.

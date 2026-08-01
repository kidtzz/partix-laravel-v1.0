const fs = require('fs');
const path = require('path');

const filesToProcess = [
    { file: 'css.blade.php', css: 'public/css/app.css', js: null, html: 'css.blade.php', isGlobalCss: true },
    { file: 'js.blade.php', css: null, js: 'public/js/app.js', html: 'js.blade.php', isGlobalJs: true },
    { file: 'dashboard.blade.php', css: 'public/css/modules/dashboard.css', js: 'public/js/modules/dashboard.js', html: 'dashboard.blade.php' },
    { file: 'penjualan.blade.php', css: 'public/css/modules/penjualan.css', js: 'public/js/modules/penjualan.js', html: 'penjualan.blade.php' },
    { file: 'admin.blade.php', css: 'public/css/modules/admin.css', js: 'public/js/modules/admin.js', html: 'admin.blade.php' },
    { file: 'stock.blade.php', css: 'public/css/modules/stock.css', js: 'public/js/modules/stock.js', html: 'stock.blade.php' },
    { file: 'return.blade.php', css: 'public/css/modules/return.css', js: 'public/js/modules/return.js', html: 'return.blade.php' },
];

for (const item of filesToProcess) {
    const filePath = path.join(__dirname, 'resources', 'views', 'partials', item.file);
    if (!fs.existsSync(filePath)) continue;

    let content = fs.readFileSync(filePath, 'utf-8');
    let newHtml = content;
    
    // Extract CSS
    if (item.css) {
        const cssMatch = content.match(/<style>([\s\S]*?)<\/style>/);
        if (cssMatch) {
            fs.mkdirSync(path.dirname(path.join(__dirname, item.css)), { recursive: true });
            fs.writeFileSync(path.join(__dirname, item.css), cssMatch[1].trim(), 'utf-8');
            newHtml = newHtml.replace(/<style>[\s\S]*?<\/style>\s*/, '');
            if (item.isGlobalCss) {
                newHtml = <link href="{{ asset('') }}?v={{ time() }}" rel="stylesheet">\n + newHtml;
            } else {
                newHtml = <link href="{{ asset('') }}?v={{ time() }}" rel="stylesheet">\n + newHtml;
            }
        }
    }
    
    // Extract JS
    if (item.js) {
        const jsMatch = content.match(/<script>([\s\S]*?)<\/script>/);
        if (jsMatch) {
            fs.mkdirSync(path.dirname(path.join(__dirname, item.js)), { recursive: true });
            fs.writeFileSync(path.join(__dirname, item.js), jsMatch[1].trim(), 'utf-8');
            newHtml = newHtml.replace(/<script>[\s\S]*?<\/script>\s*/, '');
            if (item.isGlobalJs) {
                newHtml = newHtml + \n<script src="{{ asset('') }}?v={{ time() }}"></script>\n;
            } else {
                newHtml = newHtml + \n<script src="{{ asset('') }}?v={{ time() }}"></script>\n;
            }
        }
    }
    
    fs.writeFileSync(filePath, newHtml, 'utf-8');
}

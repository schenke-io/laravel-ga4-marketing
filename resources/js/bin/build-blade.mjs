import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const jsPath = path.resolve(__dirname, '../dist/ga4-tracker.iife.js');
const sourcePath = path.resolve(__dirname, '../../views/components/scripts.blade.source.php');
const targetPath = path.resolve(__dirname, '../../views/components/scripts.blade.php');

if (!fs.existsSync(jsPath)) {
    console.error(`Minified JS not found at: ${jsPath}`);
    process.exit(1);
}

if (!fs.existsSync(sourcePath)) {
    console.error(`Source Blade template not found at: ${sourcePath}`);
    process.exit(1);
}

const jsContent = fs.readFileSync(jsPath, 'utf8');
const sourceContent = fs.readFileSync(sourcePath, 'utf8');

const finalContent = sourceContent.replace('{!! $jsContent !!}', jsContent.trim());

fs.writeFileSync(targetPath, finalContent);

console.log(`Successfully generated: ${targetPath}`);

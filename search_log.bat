@echo off
set "inputPath=D:\xampp\htdocs\nds_wip\storage\logs\laravel.log"
set "outputPath=D:\xampp\htdocs\nds_wip\storage\logs\limited_search_results.log"
set "keyword=F260711167"

echo Searching and capping at 100MB... Please wait.

powershell -NoProfile -Command "$inputPath = '%inputPath%'; $outputPath = '%outputPath%'; $keyword = '%keyword%'; $maxBytes = 100MB; $currentBytes = 0; $writer = [System.IO.StreamWriter]::new($outputPath, $false, [System.Text.Encoding]::UTF8); try { foreach ($line in [System.IO.File]::ReadLines($inputPath)) { if ($line -match $keyword) { $lineBytes = [System.Text.Encoding]::UTF8.GetByteCount($line) + 2; if (($currentBytes + $lineBytes) -gt $maxBytes) { [Console]::WriteLine('Reached 100MB limit. Stopping search.'); break; } $writer.WriteLine($line); $currentBytes += $lineBytes; } } [Console]::WriteLine('Done! Results saved to ' + $outputPath); } finally { $writer.Close(); }"

pause
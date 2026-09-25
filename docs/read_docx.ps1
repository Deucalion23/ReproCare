Add-Type -AssemblyName System.IO.Compression.FileSystem
$zipPath = 'c:\xampp\htdocs\CapstoneProject\reprocare\docs\Maternal Care in San Carlos City, Pangasinan.docx'
$zip = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
$entry = $zip.Entries | Where-Object { $_.FullName -eq 'word/document.xml' }
$stream = $entry.Open()
$reader = New-Object System.IO.StreamReader($stream)
$content = $reader.ReadToEnd()
$reader.Close()
$zip.Dispose()

$xml = [xml]$content
$ns = New-Object System.Xml.XmlNamespaceManager($xml.NameTable)
$ns.AddNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main')
$nodes = $xml.SelectNodes('//w:p', $ns)
foreach ($p in $nodes) {
    $t = ($p.SelectNodes('.//w:t', $ns) | ForEach-Object { $_.InnerText }) -join ''
    if ($t.Trim()) {
        Write-Output $t
    }
}

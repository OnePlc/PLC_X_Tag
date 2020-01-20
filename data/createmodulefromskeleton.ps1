$sModuleName = "Tag"
$sModuleKey = "tag"
$sTargetDir = "C:\Users\Praesidiarius\PhpstormProjects\OS\PLC_X_Tag_NoGIT"

# Copy Tag
Copy-Item -Path "..\*" -Destination "$sTargetDir" -recurse -Force
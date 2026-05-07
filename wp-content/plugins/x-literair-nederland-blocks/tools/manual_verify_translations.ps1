param(
    [string]$RepoRoot = "c:\werk\websites\literairnederland.nl\literairnederland.nl\wp",
    [string]$Commit = "HEAD",
    [switch]$SummaryOnly
)

$ErrorActionPreference = "Stop"
$hadWarning = $false
$summaryFailureReason = ""

$pluginRel = "wp-content/plugins/x-literair-nederland-blocks"
$pluginDir = Join-Path $RepoRoot $pluginRel
$langDir = Join-Path $pluginDir "languages"
$globalLangDir = Join-Path $RepoRoot "wp-content/languages/plugins"
$poFile = Join-Path $langDir "x-literair-nederland-blocks-nl_NL.po"
$moFile = Join-Path $langDir "x-literair-nederland-blocks-nl_NL.mo"
$globalMoFile = Join-Path $globalLangDir "x-literair-nederland-blocks-nl_NL.mo"

function Write-Warn([string]$Message) {
    $script:hadWarning = $true
    Write-Host "[WARN] $Message" -ForegroundColor Yellow
}

if (-not $SummaryOnly) {
    Write-Host "== Translation Manual Verification =="
    Write-Host "RepoRoot: $RepoRoot"
    Write-Host "Commit:   $Commit"
}

function Assert-Path([string]$Path, [string]$Label) {
    if (Test-Path $Path) {
        if (-not $SummaryOnly) {
            Write-Host "[PASS] $Label" -ForegroundColor Green
        }
    } else {
        if (-not $SummaryOnly) {
            Write-Host "[FAIL] $Label" -ForegroundColor Red
        }
        throw "$Label missing: $Path"
    }
}

try {
    Assert-Path $poFile "PO exists"
    Assert-Path $moFile "MO exists"
    Assert-Path $globalLangDir "Global plugin language directory exists"
    Assert-Path $globalMoFile "Global MO exists"

    $pluginJson = Get-ChildItem -Path $langDir -Filter "x-literair-nederland-blocks-nl_NL-*.json" -File
    $globalJson = Get-ChildItem -Path $globalLangDir -Filter "x-literair-nederland-blocks-nl_NL-*.json" -File

    if ($pluginJson.Count -gt 0) {
        if (-not $SummaryOnly) {
            Write-Host "[PASS] Plugin JS JSON files present: $($pluginJson.Count)" -ForegroundColor Green
        }
    } else {
        if (-not $SummaryOnly) {
            Write-Host "[FAIL] No plugin JS JSON files found" -ForegroundColor Red
        }
        throw "No plugin JSON translation files found"
    }

    if ($globalJson.Count -gt 0) {
        if (-not $SummaryOnly) {
            Write-Host "[PASS] Global JS JSON files present: $($globalJson.Count)" -ForegroundColor Green
        }
    } else {
        if (-not $SummaryOnly) {
            Write-Host "[FAIL] No global JS JSON files found" -ForegroundColor Red
        }
        throw "No global JSON translation files found"
    }

    $pluginJsonNames = $pluginJson | ForEach-Object { $_.Name }
    $globalJsonNames = $globalJson | ForEach-Object { $_.Name }
    $missingInGlobal = $pluginJsonNames | Where-Object { $_ -notin $globalJsonNames }

    if ($missingInGlobal.Count -eq 0) {
        if (-not $SummaryOnly) {
            Write-Host "[PASS] All plugin JSON files also exist in global language dir" -ForegroundColor Green
        }
    } else {
        if (-not $SummaryOnly) {
            Write-Host "[FAIL] Missing JSON files in global dir:" -ForegroundColor Red
            $missingInGlobal | ForEach-Object { Write-Host "       $_" }
        }
        throw "Some plugin JSON files are not mirrored in global dir"
    }

    if (-not $SummaryOnly) {
        Write-Host ""
        Write-Host "-- Check untranslated count --"
    }
    Push-Location $pluginDir
    $untranslatedOutput = c:/werk/websites/literairnederland.nl/.venv/Scripts/python.exe tools/list_untranslated.py | Out-String
    Pop-Location
    if (-not $SummaryOnly) {
        Write-Host $untranslatedOutput.TrimEnd()
    }
    if ($untranslatedOutput -match "Untranslated:\s*(\d+)") {
        $untranslatedCount = [int]$Matches[1]
        if ($untranslatedCount -ne 0) {
            throw "Untranslated entries remain: $untranslatedCount"
        }
    } else {
        throw "Could not parse untranslated count output"
    }

    if (-not $SummaryOnly) {
        Write-Host ""
        Write-Host "-- Lint changed PHP files in commit --"
    }
    $changedPhp = git -C $RepoRoot show --name-only --pretty='' $Commit | Where-Object { $_ -like "$pluginRel/*.php" }
    if (-not $changedPhp) {
        Write-Warn "No changed PHP files found for commit $Commit"
    } else {
        $failed = @()
        foreach ($f in $changedPhp) {
            if ($SummaryOnly) {
                php -l (Join-Path $RepoRoot $f) | Out-Null
            } else {
                php -l (Join-Path $RepoRoot $f) | Out-Host
            }
            if ($LASTEXITCODE -ne 0) {
                $failed += $f
            }
        }

        if ($failed.Count -eq 0) {
            if (-not $SummaryOnly) {
                Write-Host "[PASS] PHP lint passed for all changed PHP files" -ForegroundColor Green
            }
        } else {
            if (-not $SummaryOnly) {
                Write-Host "[FAIL] PHP lint failed for:" -ForegroundColor Red
                $failed | ForEach-Object { Write-Host "       $_" }
            }
            throw "PHP lint failures"
        }
    }

    if (-not $SummaryOnly) {
        Write-Host ""
        Write-Host "-- WP-CLI translation sanity --"
    }
    try {
        $siteUrl = wp --path=$RepoRoot option get siteurl
        if (-not $SummaryOnly) {
            Write-Host "Site URL: $siteUrl"
        }
        $wpEvalOutput = wp --path=$RepoRoot eval "echo __('Archive mode','x-literair-nederland-blocks');" | Out-String
        if (-not $SummaryOnly) {
            Write-Host $wpEvalOutput.TrimEnd()
            Write-Host ""
            Write-Host "[INFO] If output above is Dutch ('Archiefmodus'), PHP textdomain loading is active for current locale."
        }
    } catch {
        Write-Warn "WP-CLI sanity check could not run fully: $($_.Exception.Message)"
    }

    if (-not $SummaryOnly) {
        Write-Host ""
        Write-Host "== Manual UI checks to perform now =="
        Write-Host "1) Open Block Editor and add/edit LN Year Archive block; verify labels show Dutch in inspector."
        Write-Host "2) Add LN Oogst and LN Bibliographics blocks; verify translated JS labels/buttons."
        Write-Host "3) Publish/update and view frontend; verify strings like 'Archiefmodus'/'Beeld'/'Tekst'."
        Write-Host "4) Open browser devtools console in editor and frontend; verify no i18n or script errors."
        Write-Host ""
        Write-Host "Verification script completed." -ForegroundColor Cyan
    }

    if ($SummaryOnly) {
        if ($hadWarning) {
            Write-Host "SUMMARY: PASS_WITH_WARNINGS - commit=$Commit" -ForegroundColor Yellow
            exit 0
        } else {
            Write-Host "SUMMARY: PASS - commit=$Commit" -ForegroundColor Green
            exit 0
        }
    }
} catch {
    $summaryFailureReason = $_.Exception.Message
    if ($SummaryOnly) {
        Write-Host "SUMMARY: FAIL - commit=$Commit - reason=$summaryFailureReason" -ForegroundColor Red
    } else {
        Write-Host "[FAIL] Verification failed: $summaryFailureReason" -ForegroundColor Red
    }
    exit 1
}

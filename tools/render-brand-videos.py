"""Build 30 fps MP4 previews from the original Cambo Hub GIF assets."""

import json
import subprocess
import sys
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / '.tools' / 'video'))
import imageio_ffmpeg

ffmpeg = imageio_ffmpeg.get_ffmpeg_exe()
asset_dir = ROOT / 'public' / 'images' / 'branding' / 'animations'
sources = {
    'cambo-hub-logo-reveal.gif': 'cambo-hub-logo-reveal',
    'cambo-hub-logo-animation.gif': 'cambo-hub-logo-animation',
    'Animated Sunrise Workspace with Light Trails.gif': 'workspace-sunrise',
}

for source, name in sources.items():
    with Image.open(asset_dir / source) as original:
        original.seek(original.n_frames - 1)
        original.convert('RGB').save(asset_dir / f'{name}-poster.webp', quality=90)
    subprocess.run([
        ffmpeg, '-hide_banner', '-loglevel', 'error', '-y',
        '-i', str(asset_dir / source),
        '-vf', 'setpts=2.5*PTS,minterpolate=fps=30:mi_mode=mci:mc_mode=aobmc:me_mode=bidir:vsbmc=1:scd=fdiff,tpad=stop_mode=clone:stop_duration=0.8,pad=ceil(iw/2)*2:ceil(ih/2)*2',
        '-an', '-c:v', 'libx264', '-preset', 'medium', '-crf', '18',
        '-pix_fmt', 'yuv420p', '-r', '30', '-fps_mode', 'cfr', '-movflags', '+faststart',
        str(asset_dir / f'{name}.mp4'),
    ], check=True)
    print(f'Created {name}.mp4', flush=True)

manifest_path = ROOT / 'public' / 'cambo-assets.json'
manifest = json.loads(manifest_path.read_text(encoding='utf-8-sig'))
for animation in manifest['animations']:
    source_name = Path(animation.get('originalGif', animation['path'])).name
    name = sources[source_name]
    animation['originalGif'] = f'images/branding/animations/{source_name}'
    animation['path'] = f'images/branding/animations/{name}.mp4'
    animation['poster'] = f'images/branding/animations/{name}-poster.webp'
    animation['fps'] = 30
    animation['use'] = '30 fps interpolated video. Play on request; original GIF preserved. Source resolution retained.'
manifest['usage']['animation'] = 'MP4 previews use 30 fps motion interpolation from nine source frames. Playback is on request, with native pause controls and static posters. Interpolation may introduce warping; these are not newly filmed videos.'
manifest_path.write_text(json.dumps(manifest, indent=2, ensure_ascii=False) + '\n', encoding='utf-8')

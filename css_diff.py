import re
import os

def parse_css(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Remove comments
    content = re.sub(r'/\*.*?\*/', '', content, flags=re.DOTALL)
    
    rules = {}
    
    # Split by }
    blocks = content.split('}')
    for block in blocks:
        if '{' not in block:
            continue
        
        selector, body = block.split('{', 1)
        selector = selector.strip()
        body = body.strip()
        
        # We skip @media for simplicity right now and just grab normal classes
        if selector.startswith('@media'):
            continue
            
        props = {}
        for line in body.split(';'):
            line = line.strip()
            if line and ':' in line:
                key, val = line.split(':', 1)
                props[key.strip()] = val.strip()
                
        rules[selector] = props
            
    return rules

mod_rules = parse_css('public/css/style-modern.css')
pix_rules = parse_css('public/css/style-pixel.css')

# Find diff for pixel theme
diff_css = '/* Pixel Theme Overrides */\n\n'

for sel, pix_props in pix_rules.items():
    if sel not in mod_rules:
        # Completely new rule in pixel theme
        rules_str = '\n'.join([f'    {k}: {v};' for k, v in pix_props.items()])
        diff_css += f'{sel} {{\n{rules_str}\n}}\n\n'
    else:
        # Overridden rule
        mod_props = mod_rules[sel]
        diff_props = {}
        for k, v in pix_props.items():
            if k not in mod_props or mod_props[k] != v:
                diff_props[k] = v
        
        if diff_props:
            rules_str = '\n'.join([f'    {k}: {v};' for k, v in diff_props.items()])
            diff_css += f'{sel} {{\n{rules_str}\n}}\n\n'

with open('public/css/style-pixel.css', 'w', encoding='utf-8') as f:
    f.write(diff_css)

print('Extracted pixel overrides! style-pixel.css is now purely specific rules.')

# Now let's do the same for style-modern.css. We want to KEEP variables but REMOVE geometry.
# Wait, if style.css is identical to the ORIGINAL style-modern.css, we can just clear style-modern.css to just its variables.
modern_css = '/* Modern Theme Overrides */\n\n'
for sel, props in mod_rules.items():
    if sel == ':root' or sel == '[data-theme="light"]':
        rules_str = '\n'.join([f'    {k}: {v};' for k, v in props.items()])
        modern_css += f'{sel} {{\n{rules_str}\n}}\n\n'

with open('public/css/style-modern.css', 'w', encoding='utf-8') as f:
    f.write(modern_css)

print('Cleared style-modern.css!')

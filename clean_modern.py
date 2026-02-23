import re

with open('public/css/style-modern.css', 'r', encoding='utf-8') as f:
    content = f.read()

root_match = re.search(r':root\s*\{[^}]+\}', content)
light_match = re.search(r'\[data-theme="light"\]\s*\{[^}]+\}', content)

new_content = '/* Modern Theme Variables */\n\n'
if root_match:
    new_content += root_match.group(0) + '\n\n'
if light_match:
    new_content += light_match.group(0) + '\n'

with open('public/css/style-modern.css', 'w', encoding='utf-8') as f:
    f.write(new_content)

print('style-modern.css cleared successfully')

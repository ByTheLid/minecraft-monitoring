/**
 * Component: FontAwesome Picker
 * Usage:
 * <div class="icon-picker" data-target="#icon_input_id" data-current="fa-solid fa-star"></div>
 * <input type="hidden" name="icon" id="icon_input_id" value="fa-solid fa-star">
 */

class FontAwesomePicker {
    constructor(element) {
        this.element = element;
        this.targetId = element.getAttribute('data-target');
        this.targetInput = document.querySelector(this.targetId);
        this.currentIcon = element.getAttribute('data-current') || 'fa-solid fa-star';
        
        // Curated list of game/achievement related icons
        this.icons = [
            'fa-solid fa-star', 'fa-solid fa-trophy', 'fa-solid fa-medal', 'fa-solid fa-award',
            'fa-solid fa-crown', 'fa-solid fa-gem', 'fa-solid fa-fire', 'fa-solid fa-bolt',
            'fa-solid fa-heart', 'fa-solid fa-shield-halved', 'fa-solid fa-sword', 'fa-solid fa-dragon',
            'fa-solid fa-cube', 'fa-solid fa-cubes', 'fa-solid fa-server', 'fa-solid fa-network-wired',
            'fa-solid fa-users', 'fa-solid fa-user-astronaut', 'fa-solid fa-user-ninja', 'fa-solid fa-hand-fist',
            'fa-solid fa-hand-pointer', 'fa-solid fa-check-circle', 'fa-solid fa-bullseye', 'fa-solid fa-box-open',
            'fa-solid fa-gift', 'fa-solid fa-coins', 'fa-solid fa-money-bill', 'fa-solid fa-wallet',
            'fa-solid fa-comment-dots', 'fa-solid fa-pen-nib', 'fa-solid fa-bullhorn', 'fa-solid fa-calendar-day',
            'fa-solid fa-calendar-week', 'fa-solid fa-calendar-check', 'fa-solid fa-clock', 'fa-solid fa-stopwatch',
            'fa-solid fa-bug', 'fa-solid fa-egg', 'fa-solid fa-rocket', 'fa-solid fa-meteor',
            'fa-solid fa-compass', 'fa-solid fa-map', 'fa-solid fa-flag', 'fa-solid fa-hammer'
        ];
        
        this.init();
    }
    
    init() {
        this.element.innerHTML = `
            <div class="icon-picker-preview" id="picker_preview_${this.targetId.substring(1)}">
                <i class="${this.currentIcon}"></i>
                <span>Выбрать иконку</span>
            </div>
        `;
        
        this.preview = this.element.querySelector('.icon-picker-preview');
        
        // Create dropdown element appending it to body
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'icon-picker-dropdown';
        this.dropdown.style.display = 'none';
        this.dropdown.innerHTML = `
            <div class="icon-picker-search">
                <input type="text" placeholder="Поиск иконок..." class="form-control" style="padding: 8px; margin-bottom: 10px; font-size: 12px;">
            </div>
            <div class="icon-picker-grid"></div>
        `;
        document.body.appendChild(this.dropdown);
        
        this.grid = this.dropdown.querySelector('.icon-picker-grid');
        this.searchInput = this.dropdown.querySelector('.icon-picker-search input');
        
        this.renderIcons(this.icons);
        this.bindEvents();
        
        // Style setup if not present
        if (!document.getElementById('fa-picker-styles')) {
            const styles = document.createElement('style');
            styles.id = 'fa-picker-styles';
            styles.innerHTML = `
                .icon-picker { position: relative; width: 100%; }
                .icon-picker-preview { 
                    display: flex; align-items: center; gap: 10px; 
                    padding: 10px 15px; background: var(--bg-input); 
                    border: 1px solid var(--border-color); border-radius: var(--radius-sm);
                    cursor: pointer; color: var(--text-primary); transition: var(--transition);
                }
                .icon-picker-preview:hover { border-color: var(--accent-blue); }
                .icon-picker-preview i { font-size: 18px; color: var(--accent-blue); width: 20px; text-align: center; }
                .icon-picker-dropdown {
                    position: absolute; width: 250px; 
                    background: var(--bg-card); border: var(--glass-border); 
                    border-radius: var(--radius-sm); margin-top: 5px; 
                    padding: 10px; z-index: 99999; box-shadow: 0 10px 40px rgba(0,0,0,0.5);
                }
                .icon-picker-grid {
                    display: grid; grid-template-columns: repeat(auto-fill, minmax(36px, 1fr)); gap: 5px;
                    max-height: 200px; overflow-y: auto; padding-right: 5px;
                }
                .icon-picker-item {
                    display: flex; align-items: center; justify-content: center;
                    width: 36px; height: 36px; border-radius: var(--radius-sm);
                    cursor: pointer; transition: var(--transition); color: var(--text-secondary);
                }
                .icon-picker-item:hover { background: rgba(59, 130, 246, 0.1); color: var(--accent-blue); transform: scale(1.1); }
                .icon-picker-item.active { background: var(--accent-blue); color: #fff; }
                
                /* Scrollbar for grid */
                .icon-picker-grid::-webkit-scrollbar { width: 4px; }
                .icon-picker-grid::-webkit-scrollbar-track { background: var(--bg-primary); border-radius: 4px; }
                .icon-picker-grid::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }
            `;
            document.head.appendChild(styles);
        }
    }
    
    renderIcons(iconsToRender) {
        this.grid.innerHTML = iconsToRender.map(icon => `
            <div class="icon-picker-item ${icon === this.currentIcon ? 'active' : ''}" data-icon="${icon}" title="${icon.replace('fa-solid fa-', '')}">
                <i class="${icon}"></i>
            </div>
        `).join('');
        
        this.grid.querySelectorAll('.icon-picker-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.stopPropagation();
                this.selectIcon(item.getAttribute('data-icon'));
            });
        });
    }
    
    selectIcon(iconClass) {
        this.currentIcon = iconClass;
        this.targetInput.value = iconClass;
        this.preview.querySelector('i').className = iconClass;
        this.dropdown.style.display = 'none';
        
        // Update active class in grid
        this.grid.querySelectorAll('.icon-picker-item').forEach(el => el.classList.remove('active'));
        const activeItem = this.grid.querySelector(`.icon-picker-item[data-icon="${iconClass}"]`);
        if (activeItem) activeItem.classList.add('active');
    }
    
    bindEvents() {
        this.preview.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = this.dropdown.style.display === 'block';
            document.querySelectorAll('.icon-picker-dropdown').forEach(d => d.style.display = 'none');
            
            if (!isVisible) {
                // Calculate position relative to viewport and scroll
                const rect = this.preview.getBoundingClientRect();
                this.dropdown.style.left = rect.left + window.scrollX + 'px';
                this.dropdown.style.top = rect.bottom + window.scrollY + 'px';
                this.dropdown.style.width = Math.max(rect.width, 250) + 'px';
                
                this.dropdown.style.display = 'block';
                this.searchInput.focus();
            } else {
                this.dropdown.style.display = 'none';
            }
        });
        
        // Listen for scroll/resize to update position if opened
        const updatePosition = () => {
            if (this.dropdown.style.display === 'block') {
                const rect = this.preview.getBoundingClientRect();
                this.dropdown.style.left = rect.left + window.scrollX + 'px';
                this.dropdown.style.top = rect.bottom + window.scrollY + 'px';
            }
        };
        window.addEventListener('resize', updatePosition);
        // Bind scroll to modal wrapper if exists
        const modal = this.element.closest('.modal');
        if (modal) {
            modal.addEventListener('scroll', updatePosition);
        }
        
        this.searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const filtered = this.icons.filter(icon => icon.toLowerCase().includes(term));
            this.renderIcons(filtered);
        });
        
        this.searchInput.addEventListener('click', e => e.stopPropagation());
        this.dropdown.addEventListener('click', e => e.stopPropagation());
        
        document.addEventListener('click', () => {
            this.dropdown.style.display = 'none';
        });
    }
}

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.icon-picker').forEach(el => new FontAwesomePicker(el));
});

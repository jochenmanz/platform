import { Component } from 'src/core/shopware';
import template from './sw-sidebar-item.html.twig';
import './sw-sidebar-item.less';

/**
 * @public
 * @status ready
 * @example-type code-only
 * @component-example
 * <sw-sidebar-item title="Product" icon="default-symbol-products">
 *     Product in sidebar
 * </sw-sidebar-item>
 */
Component.register('sw-sidebar-item', {
    template,

    props: {
        title: {
            type: String,
            required: true
        },

        icon: {
            type: String,
            required: true
        },

        disabled: {
            type: Boolean,
            required: false,
            default: false
        },

        position: {
            type: String,
            required: false,
            default: 'top',
            validator(value) {
                return ['top', 'bottom'].includes(value);
            }
        }
    },

    watch: {
        disabled(newDisabledState) {
            if (newDisabledState) {
                this.closeContent();
            }
        }
    },

    data() {
        return {
            isActive: false
        };
    },

    computed: {
        sidebarItemClasses() {
            return {
                'is--active': this.showContent,
                'is--disabled': this.disabled
            };
        },

        hasDefaultSlot() {
            return !!this.$slots.default;
        },

        showContent() {
            return this.hasDefaultSlot && this.isActive;
        }
    },

    created() {
        this.componentCreated();
    },

    methods: {
        componentCreated() {
            let parent = this.$parent;

            while (parent) {
                if (parent.$options.name === 'sw-sidebar') {
                    parent.registerSidebarItem(this);
                    return;
                }

                parent = parent.$parent;
            }

            throw new Error('Component sw-sidebar-item must be registered as a (indirect) child of sw-sidebar');
        },

        openContent() {
            if (this.showContent) {
                return;
            }

            this.$emit('sw-sidebar-item-toggle-active', this);
        },

        closeContent() {
            this.isActive = false;
        },

        sidebarButtonClick(sidebarItem) {
            if (this === sidebarItem) {
                this.isActive = !this.isActive;
                this.$emit('click');
                return;
            }

            if (sidebarItem.hasDefaultSlot) {
                this.isActive = false;
            }
        }
    }
});

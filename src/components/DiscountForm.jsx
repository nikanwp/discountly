import { useSearchParams } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import {
    Button,
    TextControl,
    SelectControl,
    ToggleControl,
    RadioControl, FormTokenField, Spinner
} from "@wordpress/components";
import DatePicker from "react-multi-date-picker";
import TimePicker from "react-multi-date-picker/plugins/time_picker";
import DateObject from "react-date-object";
import persian from "react-date-object/calendars/persian";
import persian_fa from "react-date-object/locales/persian_fa";
import wcPriceFormatter from "../utils/wcPrice";
import RichTextEditor from "./RichTextEditor";

const DiscountForm = (
    {
        isLoading,
        formData,
        handleOnChange,
        handleSubmit,
        users,
        fetchUsers,
        roles,
        fetchRoles,
        categories,
        fetchCategories,
        tags,
        fetchTags,
        products,
        fetchProducts
    }) => {
    const [params] = useSearchParams();
    const discountId = params.get('id');
    const isEditing = !!discountId;

    if( isEditing && isLoading ){
        return <Spinner />
    }

    return(
        <div className="max-w-4xl px-4">
            <form onSubmit={handleSubmit} className="nwpdiscountly-layout__form">
                {/*discountType*/}
                <div className="nwpdiscountly-form-field pt-0">
                    <div className="nwpdiscountly-form-field__label">{__('Discount type','nwpdiscountly')}</div>
                    <div className="nwpdiscountly-form-field__content">
                        <SelectControl
                            __nextHasNoMarginBottom
                            value={formData.discountDetails.discountType}
                            onChange={(value) => handleOnChange('discountType',value,'discountDetails')}
                            options={[
                                {label: __('Global Discount','nwpdiscountly'), value: 'global_discount'},
                                {label: __('Cart Discount','nwpdiscountly'), value: 'cart_discount'},
                            ]}
                        />
                    </div>
                </div>
                {/*Discount name*/}
                <div className="nwpdiscountly-form-field">
                    <div className="nwpdiscountly-form-field__label">{__('Discount name','nwpdiscountly')}</div>
                    <div className="nwpdiscountly-form-field__content">
                        <TextControl
                            __nextHasNoMarginBottom
                            value={formData.discountDetails.discountName}
                            onChange={(value) => handleOnChange('discountName', value, 'discountDetails')}
                        />
                    </div>
                </div>
                {/*Active*/}
                <div className="nwpdiscountly-form-field">
                    <div className="nwpdiscountly-form-field__label">{__('Status','nwpdiscountly')}</div>
                    <div className="nwpdiscountly-form-field__content">
                        <ToggleControl
                            __nextHasNoMarginBottom
                            label={__('Active','nwpdiscountly')}
                            checked={formData.discountDetails.active}
                            onChange={(value) => handleOnChange('active',value,'discountDetails')}
                        />
                    </div>
                </div>
                {/*Minimum purchase amount for discount*/}
                {formData.discountDetails.discountType === 'cart_discount' && (
                    <div className="nwpdiscountly-form-field">
                        <div className="nwpdiscountly-form-field__label">{__('Minimum purchase amount for discount','nwpdiscountly')}</div>
                        <div className="nwpdiscountly-form-field__content">
                            <div className="nwpdiscountly-input-amount">
                                <span>{nwpdiscountly.wc_currency_symbol}</span>
                                <TextControl
                                    __nextHasNoMarginBottom
                                    onChange={(value) => { handleOnChange('min_purchase_amount', value, 'discountMeta'); }}
                                    onBlur={(e) => {
                                        if (formData.discountMeta.min_purchase_amount && e.target.value.trim() !== '') {
                                            const formattedValue = wcPriceFormatter(e.target.value);
                                            if (formattedValue !== formData.discountMeta.min_purchase_amount) {
                                                handleOnChange('min_purchase_amount', formattedValue, 'discountMeta');
                                            }
                                        }
                                    }}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            if (formData.discountMeta.min_purchase_amount && e.target.value.trim() !== '') {
                                                const formattedValue = wcPriceFormatter(e.target.value);
                                                if (formattedValue !== formData.discountMeta.min_purchase_amount) {
                                                    handleOnChange('min_purchase_amount', formattedValue, 'discountMeta');
                                                }
                                            }
                                        }
                                    }}
                                    value={ formData.discountMeta.min_purchase_amount }
                                    type="text"
                                    className="w-full"
                                />
                            </div>
                        </div>
                    </div>
                )}
                {/*Amount*/}
                <div className="nwpdiscountly-form-field">
                    <div className="nwpdiscountly-form-field__label">{__('Discount','nwpdiscountly')}</div>
                    <div className="nwpdiscountly-form-field__content">
                        <RadioControl
                            __nextHasNoMarginBottom
                            onChange={(value) => handleOnChange('amount_type',value,'discountMeta')}
                            options={[
                                {label: __('Percentage discount','nwpdiscountly'), value: 'percentage_discount'},
                                {label: __('Fixed discount','nwpdiscountly'), value: 'fixed_discount'},
                                {label: __('Percentage discount with a discount cap','nwpdiscountly'), value: 'percentage_discount_cap'}
                            ]}
                            selected={formData.discountMeta.amount_type}
                        />
                        <div className="py-2"></div>
                        {formData.discountMeta.amount_type !== 'percentage_discount_cap' && (
                            <div className="nwpdiscountly-input-amount">
                                <span>{formData.discountMeta.amount_type === 'percentage_discount' ? "%" : nwpdiscountly.wc_currency_symbol}</span>
                                <TextControl
                                    __nextHasNoMarginBottom
                                    onChange={(value) => {
                                        handleOnChange(
                                            formData.discountMeta.amount_type === 'percentage_discount' ? 'percentage_discount' : 'fixed_discount',
                                            value,
                                            'discountMeta'
                                        );
                                    }}
                                    onBlur={(e) => {
                                        if (formData.discountMeta.amount_type === 'fixed_discount' && e.target.value.trim() !== '') {
                                            const formattedValue = wcPriceFormatter(e.target.value);
                                            if (formattedValue !== formData.discountMeta.fixed_discount) {
                                                handleOnChange('fixed_discount', formattedValue, 'discountMeta');
                                            }
                                        }
                                    }}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            if (formData.discountMeta.amount_type === 'fixed_discount' && e.target.value.trim() !== '') {
                                                const formattedValue = wcPriceFormatter(e.target.value);
                                                if (formattedValue !== formData.discountMeta.fixed_discount) {
                                                    handleOnChange('fixed_discount', formattedValue, 'discountMeta');
                                                }
                                            }
                                        }
                                    }}
                                    value={
                                        formData.discountMeta.amount_type === 'percentage_discount' ? formData.discountMeta.percentage_discount : formData.discountMeta.fixed_discount
                                    }
                                    type={ formData.discountMeta.amount_type === 'percentage_discount' ? "number" : "text"}
                                    min="0"
                                    max={ formData.discountMeta.amount_type === 'percentage_discount' ? '100' : ''}
                                    className="w-full"
                                />
                            </div>
                        )}
                        {formData.discountMeta.amount_type === 'percentage_discount_cap' && (
                            <div className="flex gap-2 items-center">
                                <div className="nwpdiscountly-input-amount flex-1">
                                    <span>%</span>
                                    <TextControl
                                        __nextHasNoMarginBottom
                                        onChange={(value) => {
                                            handleOnChange('percentage_discount', value, 'discountMeta');
                                        }}
                                        value={ formData.discountMeta.percentage_discount }
                                        type="number"
                                        min="0"
                                        max="100"
                                        className="w-full"
                                    />
                                </div>
                                <div>{__('discount up to','nwpdiscountly')}</div>
                                <div className="nwpdiscountly-input-amount flex-1">
                                    <span>{nwpdiscountly.wc_currency_symbol}</span>
                                    <TextControl
                                        __nextHasNoMarginBottom
                                        onChange={(value) => {
                                            handleOnChange('percentage_discount_cap', value, 'discountMeta');
                                        }}
                                        onBlur={(e) => {
                                            if (e.target.value.trim() !== '') {
                                                const formattedValue = wcPriceFormatter(e.target.value);
                                                if (formattedValue !== formData.discountMeta.percentage_discount_cap) {
                                                    handleOnChange('percentage_discount_cap', formattedValue, 'discountMeta');
                                                }
                                            }
                                        }}
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter') {
                                                if (e.target.value.trim() !== '') {
                                                    const formattedValue = wcPriceFormatter(e.target.value);
                                                    if (formattedValue !== formData.discountMeta.percentage_discount_cap) {
                                                        handleOnChange('percentage_discount_cap', formattedValue, 'discountMeta');
                                                    }
                                                }
                                            }
                                        }}
                                        value={
                                            formData.discountMeta.percentage_discount_cap
                                        }
                                        type="text"
                                        min="0"
                                        className="w-full"
                                    />
                                </div>
                            </div>
                        )}
                    </div>
                </div>
                {/*Products*/}
                <div className="nwpdiscountly-form-field">
                    <div className="nwpdiscountly-form-field__label">{__('Products','nwpdiscountly')}</div>
                    <div className="nwpdiscountly-form-field__content">
                        <RadioControl
                            __nextHasNoMarginBottom
                            onChange={(value) => handleOnChange('products',value,'discountMeta')}
                            options={[
                                {label: __('All Products','nwpdiscountly'), value: 'all_products'},
                                {label: __('Selected products','nwpdiscountly'), value: 'selected_products'},
                                {label: __('Selected categories','nwpdiscountly'), value: 'selected_categories'},
                                {label: __('Selected tags','nwpdiscountly'), value: 'selected_tags'}
                            ]}
                            selected={formData.discountMeta.products}
                        />
                        {formData.discountMeta.products === "selected_products" && (
                            <FormTokenField
                                __nextHasNoMarginBottom
                                label={false}
                                __experimentalShowHowTo={false}
                                __experimentalValidateInput={(value) => {
                                    const selectedProduct = products.find((product) => product.label === value);
                                    if (!selectedProduct) return true;

                                    if (selectedProduct.type === 'variation') {
                                        return !formData.discountMeta.selected_products.some((p) => p.value === selectedProduct.parentId);
                                    }

                                    if (selectedProduct.type === 'product') {
                                        return !formData.discountMeta.selected_products.some((p) => p.parentId === selectedProduct.value);
                                    }

                                    return true;
                                }}
                                __experimentalRenderItem={({ item }) => {
                                    const selectedProduct = products.find((product) => product.label === item);
                                    let disabledClass = "";

                                    if (selectedProduct) {
                                        if (selectedProduct.type === 'variation') {
                                            if (formData.discountMeta.selected_products.some((p) => p.value === selectedProduct.parentId)) {
                                                disabledClass = "text-gray-500 cursor-not-allowed opacity-60";
                                            }
                                        } else if (selectedProduct.type === 'product') {
                                            const hasVariationSelected = formData.discountMeta.selected_products.some((p) => p.parentId === selectedProduct.value);
                                            if (hasVariationSelected) {
                                                disabledClass = "text-gray-500 cursor-not-allowed opacity-60";
                                            }
                                        }
                                    }

                                    return (
                                        <div className={disabledClass}>
                                            {item}
                                        </div>
                                    );
                                }}
                                value={
                                    (isEditing && products.length === 0)
                                        ? []
                                        : (formData.discountMeta.selected_products || [])
                                            .map((selectedProduct) => {
                                                const product = products.find((p) => p.value === selectedProduct.value);
                                                return product ? product.label : null;
                                            }).filter(Boolean)
                                }
                                suggestions={products.map(product => product.label)}
                                onChange={(tokens) => {
                                    const selectedProducts = tokens.map((token) => {
                                        const product = products.find((product) => product.label === token);
                                        if (product) {
                                            return {
                                                value: product.value,
                                                type: product.type,
                                                parentId: product.parentId,
                                            };
                                        }
                                        return null;
                                    }).filter(Boolean);

                                    handleOnChange("selected_products", selectedProducts, "discountMeta");
                                }}
                                onInputChange={(value) => fetchProducts(value)}
                                placeholder={__("Type to search products...", "nwpdiscountly")}
                                className="mt-4"
                            />

                        )}

                        {formData.discountMeta.products === "selected_categories" && (
                            <FormTokenField
                                __nextHasNoMarginBottom
                                label={false}
                                __experimentalShowHowTo={false}
                                __experimentalValidateInput={(value) => {
                                    const selectedCategory = categories.find((category) => category.label === value);
                                    if (!selectedCategory) return true;
                                    return true;
                                }}
                                value={
                                    ( isEditing && categories.length === 0 )
                                        ? []
                                        : (formData.discountMeta.selected_categories || [])
                                            .map((categoryId) => {
                                                const category = categories.find((category) => category.value === categoryId);
                                                return category ? category.label : null;
                                            }).filter(Boolean)
                                }
                                suggestions={categories.map((category) => category.label)}
                                onChange={(tokens) => {
                                    const categoryIds = tokens.map((token) => {
                                        const category = categories.find((category) => category.label === token);
                                        return category ? category.value : null;
                                    }).filter(Boolean);
                                    handleOnChange("selected_categories", categoryIds, "discountMeta");
                                }}
                                onInputChange={(value) => fetchCategories(value)}
                                placeholder={__("Type to search categories...", "nwpdiscountly")}
                                className="mt-4"
                            />
                        )}
                        {formData.discountMeta.products === "selected_tags" && (
                            <FormTokenField
                                __nextHasNoMarginBottom
                                label={false}
                                __experimentalShowHowTo={false}
                                __experimentalValidateInput={(value) => {
                                    const selectedTag = tags.find((tag) => tag.label === value);
                                    if (!selectedTag) return true;
                                    return true;
                                }}
                                value={
                                    ( isEditing && tags.length === 0 )
                                        ? []
                                        : (formData.discountMeta.selected_tags || [])
                                            .map((tagId) => {
                                                const tag = tags.find((tag) => tag.value === tagId);
                                                return tag ? tag.label : null;
                                            }).filter(Boolean)
                                }
                                suggestions={tags.map((tag) => tag.label)}
                                onChange={(tokens) => {
                                    const tagIds = tokens.map((token) => {
                                        const tag = tags.find((tag) => tag.label === token);
                                        return tag ? tag.value : null;
                                    }).filter(Boolean);
                                    handleOnChange("selected_tags", tagIds, "discountMeta");
                                }}
                                onInputChange={(value) => fetchTags(value)}
                                placeholder={__("Type to search tags...", "nwpdiscountly")}
                                className="mt-4"
                            />
                        )}
                    </div>
                </div>

                {/*Applies to*/}
                <div className="nwpdiscountly-form-field">
                    <div className="nwpdiscountly-form-field__label">{__('Applies to','nwpdiscountly')}</div>
                    <div className="nwpdiscountly-form-field__content">
                        <RadioControl
                            __nextHasNoMarginBottom
                            onChange={(value) => handleOnChange('applies_to',value,'discountMeta')}
                            options={[
                                {label: __('All users','nwpdiscountly'), value: 'all_users'},
                                {label: __('Selected users','nwpdiscountly'), value: 'selected_users'},
                                {label: __('Selected roles','nwpdiscountly'), value: 'selected_roles'}
                            ]}
                            selected={formData.discountMeta.applies_to}
                        />
                        {formData.discountMeta.applies_to === "selected_users" && (
                            <FormTokenField
                                __nextHasNoMarginBottom
                                label={false}
                                __experimentalShowHowTo={false}
                                __experimentalValidateInput={(value) => {
                                    const selectedUser = users.find((user) => user.label === value);
                                    if (!selectedUser) {
                                        return true;
                                    }
                                    return true;
                                }}

                                value={
                                    ( isEditing && users.length === 0 )
                                        ? []
                                        : (formData.discountMeta.selected_users || [])
                                            .map((userId) => {
                                                const user = users.find((user) => user.value === userId);
                                                return user ? user.label : null;
                                            }).filter(Boolean)
                                }
                                suggestions={users.map((user) => user.label)}
                                onChange={(tokens) => {
                                    const userIds = tokens.map((token) => {
                                        const user = users.find((user) => user.label === token);
                                        return user ? user.value : null;
                                    }).filter(Boolean);
                                    handleOnChange("selected_users", userIds, "discountMeta");
                                }}
                                onInputChange={(value) => fetchUsers(value)}
                                placeholder={__("Type to search users...", "nwpdiscountly")}
                                className="mt-4"
                            />
                        )}
                        {formData.discountMeta.applies_to === "selected_roles" && (
                            <FormTokenField
                                __nextHasNoMarginBottom
                                label={false}
                                __experimentalShowHowTo={false}
                                __experimentalValidateInput={(value) => {
                                    const selectedRole = roles.find((role) => role.label === value);
                                    if (!selectedRole) {
                                        return true;
                                    }
                                    return true;
                                }}
                                value={
                                    ( isEditing && roles.length === 0 )
                                        ? []
                                        : (formData.discountMeta.selected_roles || [])
                                            .map((roleKey) => {
                                                const role = roles.find((role) => role.value === roleKey);
                                                return role ? role.label : null;
                                            })
                                }
                                suggestions={roles.map((role) => role.label)}
                                onChange={(tokens) => {
                                    const roleKeys = tokens.map((token) => {
                                        const role = roles.find((role) => role.label === token);
                                        return role ? role.value : null;
                                    });
                                    handleOnChange("selected_roles", roleKeys, "discountMeta");
                                }}
                                placeholder={__("Type to search roles...", "nwpdiscountly")}
                                onInputChange={(value) => fetchRoles(value)}
                                className="mt-4"
                            />
                        )}
                    </div>
                </div>
                {/*Disable discount if a coupon is applied*/}
                <div className="nwpdiscountly-form-field">
                    <div className="nwpdiscountly-form-field__label">{__('Disable discount if a coupon is applied','nwpdiscountly')}</div>
                    <div className="nwpdiscountly-form-field__content">
                        <ToggleControl
                            __nextHasNoMarginBottom
                            label={__('Enable this option if you want to disable the discount when a coupon code is applied','nwpdiscountly')}
                            checked={formData.discountMeta.disable_discount_with_coupon}
                            onChange={(value) => handleOnChange('disable_discount_with_coupon',value,'discountMeta')}
                        />
                    </div>
                </div>
                {/*Availability*/}
                <div className="nwpdiscountly-form-field">
                    <div className="nwpdiscountly-form-field__label">{__('Availability','nwpdiscountly')}</div>
                    <div className="nwpdiscountly-form-field__content">
                        <RadioControl
                            __nextHasNoMarginBottom
                            onChange={(value) => handleOnChange('availability',value,'discountMeta')}
                            options={[
                                {label: __('Always Available','nwpdiscountly'), value: 'always_available'},
                                {label: __('Specific Date','nwpdiscountly'), value: 'specific_date'}
                            ]}
                            selected={formData.discountMeta.availability}
                        />
                        {formData.discountMeta.availability === 'specific_date' && (
                            <div className="flex gap-3 mt-5">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs">{__('Start Date','nwpdiscountly')}</span>
                                    <DatePicker
                                        value={nwpdiscountly.wp_lang === "fa_IR" && formData.discountMeta.start_date
                                            ? new DateObject(formData.discountMeta.start_date).convert(persian).format('YYYY-MM-DD HH:mm:ss')
                                            : formData.discountMeta.start_date}
                                        format="YYYY-MM-DD HH:mm:ss"
                                        plugins={[<TimePicker position="bottom" />]}
                                        locale={nwpdiscountly.wp_lang === "fa_IR" ? persian_fa : undefined}
                                        calendar={nwpdiscountly.wp_lang === "fa_IR" ? persian : undefined}
                                        calendarPosition={nwpdiscountly.wp_lang === "fa_IR" ? "bottom-right" : undefined}
                                        maxDate={
                                            nwpdiscountly.wp_lang === "fa_IR" && formData.discountMeta.end_date
                                                ? new DateObject(formData.discountMeta.end_date).convert(persian).format('YYYY-MM-DD HH:mm:ss')
                                                : formData.discountMeta.end_date
                                        }
                                        onChange={(date) => {
                                            const gregorianDate = new DateObject(date)
                                                .convert()
                                                .format("YYYY-MM-DD HH:mm:ss");
                                            const finalStartDate = nwpdiscountly.wp_lang === "fa_IR"
                                                ? gregorianDate.replace(/[۰-۹]/g, (d) => "۰۱۲۳۴۵۶۷۸۹".indexOf(d))
                                                : gregorianDate;

                                            handleOnChange("start_date", finalStartDate, "discountMeta");
                                        }}
                                    />
                                </div>
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs">{__('End Date','nwpdiscountly')}</span>
                                    <DatePicker
                                        value={nwpdiscountly.wp_lang === "fa_IR" && formData.discountMeta.end_date
                                            ? new DateObject(formData.discountMeta.end_date).convert(persian).format('YYYY-MM-DD HH:mm:ss')
                                            : formData.discountMeta.end_date}
                                        format="YYYY-MM-DD HH:mm:ss"
                                        plugins={[<TimePicker position="bottom" />]}
                                        locale={nwpdiscountly.wp_lang === "fa_IR" ? persian_fa : undefined}
                                        calendar={nwpdiscountly.wp_lang === "fa_IR" ? persian : undefined}
                                        calendarPosition={nwpdiscountly.wp_lang === "fa_IR" ? "bottom-right" : undefined}
                                        minDate={
                                            nwpdiscountly.wp_lang === "fa_IR" && formData.discountMeta.start_date
                                                ? new DateObject(formData.discountMeta.start_date).convert(persian).format('YYYY-MM-DD HH:mm:ss')
                                                : formData.discountMeta.start_date
                                        }
                                        onChange={(date) => {
                                            const gregorianDate = new DateObject(date)
                                                .convert()
                                                .format("YYYY-MM-DD HH:mm:ss");
                                            const finalEndDate = nwpdiscountly.wp_lang === "fa_IR"
                                                ? gregorianDate.replace(/[۰-۹]/g, (d) => "۰۱۲۳۴۵۶۷۸۹".indexOf(d))
                                                : gregorianDate;

                                            handleOnChange("end_date", finalEndDate, "discountMeta");
                                        }}
                                    />
                                </div>
                            </div>
                        )}
                    </div>
                </div>
                {/*Promotional message on product page*/}
                {formData.discountDetails.discountType === 'global_discount' && (
                    <div className="nwpdiscountly-form-field border-0">
                        <div className="nwpdiscountly-form-field__label">{__('Promotional message on product page','nwpdiscountly')}</div>
                        <div className="nwpdiscountly-form-field__content">
                            <RichTextEditor
                                value={ formData.discountMeta.product_promo_message }
                                onChange={ (value) => { handleOnChange('product_promo_message', value, 'discountMeta'); } }
                            />
                        </div>
                    </div>
                )}

                {/*Submit button*/}
                <Button
                    type="submit"
                    variant="primary"
                    disabled={isLoading}
                    isBusy={isLoading}
                    className="mt-4"
                >
                    {isEditing ? __('Update discount','nwpdiscountly') : __('Create discount','nwpdiscountly')}
                </Button>
            </form>
        </div>
    )
}
export default DiscountForm
import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export function ProductForm() {
    return (
        <form className="space-y-6">
            <FormSection title="Core details" description="Essential product fields for admin setup.">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="product-name">Name</Label>
                        <Input id="product-name" placeholder="Classic sneaker" />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="product-sku">SKU</Label>
                        <Input id="product-sku" placeholder="SNK-001" />
                    </div>
                </div>
            </FormSection>
            <FormSection title="Pricing" description="Minor-unit backend values can be handled later.">
                <div className="grid gap-4 md:grid-cols-3">
                    <div className="space-y-2">
                        <Label htmlFor="product-base-price">Base price</Label>
                        <Input id="product-base-price" placeholder="25000" />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="product-compare-price">Compare at price</Label>
                        <Input id="product-compare-price" placeholder="30000" />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="product-cost-price">Cost price</Label>
                        <Input id="product-cost-price" placeholder="18000" />
                    </div>
                </div>
            </FormSection>
            <FormSection title="Catalog structure" description="Category, brand, variants, and media will plug in here later.">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="product-slug">Slug</Label>
                        <Input id="product-slug" placeholder="classic-sneaker" />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="product-status">Status</Label>
                        <Input id="product-status" placeholder="draft" />
                    </div>
                </div>
            </FormSection>
            <FormActions />
        </form>
    );
}

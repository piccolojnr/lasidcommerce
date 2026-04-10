import { FormActions } from '@/components/shared/forms/form-actions';
import { FormSection } from '@/components/shared/forms/form-section';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export function SettingsForm() {
    return (
        <form className="space-y-6">
            <FormSection
                title="Store defaults"
                description="This is a placeholder surface for grouped settings."
            >
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                        <Label htmlFor="setting-store-name">Store name</Label>
                        <Input
                            id="setting-store-name"
                            placeholder="Lasid Commerce"
                        />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="setting-support-email">
                            Support email
                        </Label>
                        <Input
                            id="setting-support-email"
                            placeholder="support@example.com"
                        />
                    </div>
                </div>
            </FormSection>
            <FormActions />
        </form>
    );
}

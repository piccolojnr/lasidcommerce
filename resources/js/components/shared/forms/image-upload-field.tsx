import { ImagePlus, Sparkles, Trash2, Upload } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { FieldError } from '@/components/shared/forms/field-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

interface ExistingImage {
    id: number;
    url: string;
    preview_url?: string;
    is_primary?: boolean;
}

interface ImageUploadFieldProps {
    id: string;
    name: string;
    label: string;
    accept?: string;
    multiple?: boolean;
    existingImages?: ExistingImage[];
    removeFieldName?: string;
    getRemoveValue?: (image: ExistingImage) => string;
    helperText?: string;
    error?: string | null;
}

export function ImageUploadField({
    id,
    name,
    label,
    accept = 'image/jpeg,image/png,image/webp',
    multiple = true,
    existingImages = [],
    removeFieldName = 'remove_image_ids[]',
    getRemoveValue = (image) => String(image.id),
    helperText,
    error,
}: ImageUploadFieldProps) {
    const inputRef = useRef<HTMLInputElement | null>(null);
    const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
    const [removedIds, setRemovedIds] = useState<number[]>([]);
    const [isDragging, setIsDragging] = useState(false);

    const selectedPreviews = useMemo(
        () =>
            selectedFiles.map((file) => ({
                name: file.name,
                url: URL.createObjectURL(file),
            })),
        [selectedFiles],
    );

    useEffect(() => {
        return () => {
            selectedPreviews.forEach((image) => URL.revokeObjectURL(image.url));
        };
    }, [selectedPreviews]);

    useEffect(() => {
        if (!inputRef.current) {
            return;
        }

        const dataTransfer = new DataTransfer();

        selectedFiles.forEach((file) => {
            dataTransfer.items.add(file);
        });

        inputRef.current.files = dataTransfer.files;
    }, [selectedFiles]);

    const visibleExistingImages = existingImages.filter(
        (image) => !removedIds.includes(image.id),
    );
    const addFiles = (files: File[]) => {
        setSelectedFiles((currentFiles) => {
            if (!multiple) {
                return files.slice(0, 1);
            }

            const existingKeys = new Set(
                currentFiles.map(
                    (file) => `${file.name}:${file.size}:${file.lastModified}`,
                ),
            );

            const uniqueNextFiles = files.filter((file) => {
                const key = `${file.name}:${file.size}:${file.lastModified}`;

                return !existingKeys.has(key);
            });

            return [...currentFiles, ...uniqueNextFiles];
        });
    };

    return (
        <div className="space-y-5">
            <div className="space-y-2">
                <Label htmlFor={id}>{label}</Label>
                <div className="space-y-3" />
                <label
                    htmlFor={id}
                    className={cn(
                        'group block cursor-pointer rounded-lg border border-dashed border-border/80 bg-background p-5 transition hover:border-primary/50 hover:bg-muted/40',
                        isDragging && 'border-primary bg-muted/40',
                    )}
                    onDragEnter={(event) => {
                        event.preventDefault();
                        setIsDragging(true);
                    }}
                    onDragOver={(event) => {
                        event.preventDefault();
                        setIsDragging(true);
                    }}
                    onDragLeave={(event) => {
                        event.preventDefault();
                        setIsDragging(false);
                    }}
                    onDrop={(event) => {
                        event.preventDefault();
                        setIsDragging(false);
                        addFiles(Array.from(event.dataTransfer.files));
                    }}
                >
                    <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div className="flex items-start gap-3">
                            <div className="rounded-lg bg-primary/10 p-3 text-primary">
                                <ImagePlus />
                            </div>
                            <div className="space-y-1">
                                <p className="font-medium">
                                    {isDragging
                                        ? 'Release images to queue them'
                                        : 'Drop product imagery here or click to browse'}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Use clean packshots, lifestyle images, or
                                    detail shots to make the product page feel
                                    real.
                                </p>
                                {helperText ? (
                                    <p className="text-xs text-muted-foreground">
                                        {helperText}
                                    </p>
                                ) : null}
                            </div>
                        </div>
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <Upload />
                            <span>
                                {multiple
                                    ? 'Multiple images allowed'
                                    : 'Single image'}
                            </span>
                        </div>
                    </div>
                </label>
                <Input
                    ref={inputRef}
                    id={id}
                    name={name}
                    type="file"
                    multiple={multiple}
                    accept={accept}
                    className="hidden"
                    onChange={(event) => {
                        const nextFiles = Array.from(event.target.files ?? []);

                        addFiles(nextFiles);

                        // Allow selecting the same file again in a later interaction.
                        event.target.value = '';
                    }}
                />
                <FieldError message={error} />
            </div>

            {visibleExistingImages.length > 0 ? (
                <div className="space-y-3">
                    <div className="flex items-center gap-2 text-sm font-medium">
                        <Sparkles />
                        <span>Current gallery</span>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {visibleExistingImages.map((image) => (
                            <div
                                key={image.id}
                                className="overflow-hidden rounded-lg border border-border/70 bg-background"
                            >
                                <img
                                    src={image.preview_url ?? image.url}
                                    alt=""
                                    className="aspect-4/3 w-full object-cover"
                                />
                                <div className="flex items-center justify-between gap-3 p-3">
                                    <div className="space-y-1">
                                        <p className="text-sm font-medium">
                                            Existing image
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {image.is_primary
                                                ? 'Primary display image'
                                                : 'Gallery image'}
                                        </p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        className="text-destructive hover:text-destructive"
                                        onClick={() =>
                                            setRemovedIds((current) => [
                                                ...current,
                                                image.id,
                                            ])
                                        }
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            ) : null}

            {removedIds.map((idValue) => {
                const image = existingImages.find(
                    (item) => item.id === idValue,
                );

                return (
                    <input
                        key={idValue}
                        type="hidden"
                        name={removeFieldName}
                        value={image ? getRemoveValue(image) : String(idValue)}
                    />
                );
            })}

            {selectedPreviews.length > 0 ? (
                <div className="space-y-3">
                    <div className="flex items-center justify-between gap-3">
                        <div>
                            <p className="text-sm font-medium">
                                Queued uploads
                            </p>
                            <p className="text-xs text-muted-foreground">
                                {selectedPreviews.length} image
                                {selectedPreviews.length === 1 ? '' : 's'} ready
                                to upload
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => setSelectedFiles([])}
                        >
                            Clear selection
                        </Button>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {selectedPreviews.map((image, index) => (
                            <div
                                key={`${image.name}-${index}`}
                                className="overflow-hidden rounded-lg border border-border/70 bg-background"
                            >
                                <img
                                    src={image.url}
                                    alt=""
                                    className="aspect-4/3 w-full object-cover"
                                />
                                <div className="flex items-center justify-between gap-3 p-3">
                                    <div className="min-w-0">
                                        <p className="truncate text-sm font-medium">
                                            {image.name}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            Will be uploaded on save
                                        </p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        className="text-destructive hover:text-destructive"
                                        onClick={() =>
                                            setSelectedFiles((current) =>
                                                current.filter(
                                                    (_, fileIndex) =>
                                                        fileIndex !== index,
                                                ),
                                            )
                                        }
                                    >
                                        <Trash2 />
                                    </Button>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            ) : null}
        </div>
    );
}

import { ArrowDown, ArrowUp, ImagePlus, Sparkles, Star, Trash2, Upload } from 'lucide-react';
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
    /** Called whenever the queued-upload count changes. */
    onQueueChange?: (count: number) => void;
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
    onQueueChange,
}: ImageUploadFieldProps) {
    const inputRef = useRef<HTMLInputElement | null>(null);
    const [selectedFiles, setSelectedFiles] = useState<File[]>([]);
    const [removedIds, setRemovedIds] = useState<number[]>([]);
    const [isDragging, setIsDragging] = useState(false);

    // Ordered list of existing image ids — drives display order + sort hidden fields
    const [orderedIds, setOrderedIds] = useState<number[]>(
        () => existingImages.map((img) => img.id),
    );

    // Which existing image id is the primary (null = default to first visible)
    const [primaryId, setPrimaryId] = useState<number | null>(
        () => existingImages.find((img) => img.is_primary)?.id ?? null,
    );

    // Keep orderedIds in sync if existingImages prop changes after a save
    useEffect(() => {
        setOrderedIds((prev) => {
            const nextIds = existingImages.map((img) => img.id);
            const kept = prev.filter((id) => nextIds.includes(id));
            nextIds.forEach((id) => {
                if (!kept.includes(id)) kept.push(id);
            });
            return kept;
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [existingImages]);

    const selectedPreviews = useMemo(
        () => selectedFiles.map((file) => ({ name: file.name, url: URL.createObjectURL(file) })),
        [selectedFiles],
    );

    useEffect(() => {
        return () => selectedPreviews.forEach((img) => URL.revokeObjectURL(img.url));
    }, [selectedPreviews]);

    // Keep real file input in sync with our state array
    useEffect(() => {
        if (!inputRef.current) return;
        const dt = new DataTransfer();
        selectedFiles.forEach((file) => dt.items.add(file));
        inputRef.current.files = dt.files;
    }, [selectedFiles]);

    const visibleExistingImages = useMemo(
        () =>
            orderedIds
                .filter((id) => !removedIds.includes(id))
                .map((id) => existingImages.find((img) => img.id === id))
                .filter((img): img is ExistingImage => img !== undefined),
        [orderedIds, removedIds, existingImages],
    );

    const addFiles = (files: File[]) => {
        setSelectedFiles((current) => {
            if (!multiple) {
                const next = files.slice(0, 1);
                onQueueChange?.(next.length);
                return next;
            }
            const existingKeys = new Set(
                current.map((f) => `${f.name}:${f.size}:${f.lastModified}`),
            );
            const unique = files.filter(
                (f) => !existingKeys.has(`${f.name}:${f.size}:${f.lastModified}`),
            );
            const next = [...current, ...unique];
            onQueueChange?.(next.length);
            return next;
        });
    };

    const moveImage = (imageId: number, direction: 'up' | 'down') => {
        setOrderedIds((prev) => {
            const idx = prev.indexOf(imageId);
            if (idx === -1) return prev;
            const swapIdx = direction === 'up' ? idx - 1 : idx + 1;
            if (swapIdx < 0 || swapIdx >= prev.length) return prev;
            const next = [...prev];
            [next[idx], next[swapIdx]] = [next[swapIdx], next[idx]];
            return next;
        });
    };

    const removeExisting = (imageId: number) => {
        setRemovedIds((curr) => [...curr, imageId]);
        if (primaryId === imageId) setPrimaryId(null);
    };

    // Effective primary: explicit choice, else fall back to first visible image
    const effectivePrimaryId = primaryId ?? visibleExistingImages[0]?.id ?? null;

    return (
        <div className="space-y-5">
            {/* Drop zone */}
            <div className="space-y-2">
                <Label htmlFor={id}>{label}</Label>
                <label
                    htmlFor={id}
                    className={cn(
                        'group block cursor-pointer rounded-lg border border-dashed border-border/80 bg-background p-5 transition hover:border-primary/50 hover:bg-muted/40',
                        isDragging && 'border-primary bg-muted/40',
                    )}
                    onDragEnter={(e) => { e.preventDefault(); setIsDragging(true); }}
                    onDragOver={(e) => { e.preventDefault(); setIsDragging(true); }}
                    onDragLeave={(e) => { e.preventDefault(); setIsDragging(false); }}
                    onDrop={(e) => {
                        e.preventDefault();
                        setIsDragging(false);
                        addFiles(Array.from(e.dataTransfer.files));
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
                                    Use clean packshots, lifestyle images, or detail shots.
                                </p>
                                {helperText ? (
                                    <p className="text-xs text-muted-foreground">{helperText}</p>
                                ) : null}
                            </div>
                        </div>
                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                            <Upload />
                            <span>{multiple ? 'Multiple images allowed' : 'Single image'}</span>
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
                    onChange={(e) => {
                        addFiles(Array.from(e.target.files ?? []));
                        e.target.value = '';
                    }}
                />
                <FieldError message={error} />
            </div>

            {/* Existing gallery with primary + reorder controls */}
            {visibleExistingImages.length > 0 ? (
                <div className="space-y-3">
                    <div className="flex items-center gap-2 text-sm font-medium">
                        <Sparkles className="size-4" />
                        <span>Current gallery</span>
                        <span className="ml-auto text-xs text-muted-foreground">
                            ★ to set primary · arrows to reorder
                        </span>
                    </div>
                    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        {visibleExistingImages.map((image, index) => {
                            const isPrimary = image.id === effectivePrimaryId;
                            const isFirst = index === 0;
                            const isLast = index === visibleExistingImages.length - 1;

                            return (
                                <div
                                    key={image.id}
                                    className={cn(
                                        'overflow-hidden rounded-lg border bg-background transition',
                                        isPrimary
                                            ? 'border-primary/60 ring-2 ring-primary/20'
                                            : 'border-border/70',
                                    )}
                                >
                                    <div className="relative">
                                        <img
                                            src={image.preview_url ?? image.url}
                                            alt=""
                                            className="aspect-4/3 w-full object-cover"
                                        />
                                        {isPrimary && (
                                            <span className="absolute top-2 left-2 rounded-full bg-primary px-2 py-0.5 text-[10px] font-semibold tracking-wide text-primary-foreground uppercase">
                                                Primary
                                            </span>
                                        )}
                                    </div>
                                    <div className="flex items-center justify-between gap-1 p-2">
                                        {/* Reorder arrows */}
                                        <div className="flex gap-1">
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-7 w-7"
                                                disabled={isFirst}
                                                title="Move earlier"
                                                onClick={() => moveImage(image.id, 'up')}
                                            >
                                                <ArrowUp className="size-3.5" />
                                            </Button>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                className="h-7 w-7"
                                                disabled={isLast}
                                                title="Move later"
                                                onClick={() => moveImage(image.id, 'down')}
                                            >
                                                <ArrowDown className="size-3.5" />
                                            </Button>
                                        </div>

                                        {/* Set primary */}
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            className={cn(
                                                'h-7 w-7',
                                                isPrimary
                                                    ? 'text-primary'
                                                    : 'text-muted-foreground hover:text-primary',
                                            )}
                                            title={isPrimary ? 'Primary image' : 'Set as primary'}
                                            onClick={() => setPrimaryId(image.id)}
                                        >
                                            <Star
                                                className="size-3.5"
                                                fill={isPrimary ? 'currentColor' : 'none'}
                                            />
                                        </Button>

                                        {/* Remove */}
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="icon"
                                            className="h-7 w-7 text-destructive hover:text-destructive"
                                            title="Remove image"
                                            onClick={() => removeExisting(image.id)}
                                        >
                                            <Trash2 className="size-3.5" />
                                        </Button>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            ) : null}

            {/* Hidden fields: removed ids, sort order, primary id */}
            {removedIds.map((idValue) => {
                const image = existingImages.find((img) => img.id === idValue);
                return (
                    <input
                        key={idValue}
                        type="hidden"
                        name={removeFieldName}
                        value={image ? getRemoveValue(image) : String(idValue)}
                    />
                );
            })}
            {visibleExistingImages.map((image, index) => (
                <input
                    key={`sort-${image.id}`}
                    type="hidden"
                    name="image_sort_order[]"
                    value={`${image.id}:${index}`}
                />
            ))}
            {effectivePrimaryId != null ? (
                <input type="hidden" name="primary_image_id" value={String(effectivePrimaryId)} />
            ) : null}

            {/* Queued new uploads */}
            {selectedPreviews.length > 0 ? (
                <div className="space-y-3">
                    <div className="flex items-center justify-between gap-3">
                        <div>
                            <p className="text-sm font-medium">Queued uploads</p>
                            <p className="text-xs text-muted-foreground">
                                {selectedPreviews.length} image{selectedPreviews.length === 1 ? '' : 's'} ready
                                to upload
                            </p>
                        </div>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={() => {
                                setSelectedFiles([]);
                                onQueueChange?.(0);
                            }}
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
                                <img src={image.url} alt="" className="aspect-4/3 w-full object-cover" />
                                <div className="flex items-center justify-between gap-3 p-3">
                                    <div className="min-w-0">
                                        <p className="truncate text-sm font-medium">{image.name}</p>
                                        <p className="text-xs text-muted-foreground">Will be uploaded on save</p>
                                    </div>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        className="text-destructive hover:text-destructive"
                                        onClick={() =>
                                            setSelectedFiles((curr) => curr.filter((_, i) => i !== index))
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

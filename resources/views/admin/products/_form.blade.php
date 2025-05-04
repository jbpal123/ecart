<div class="mb-3">
    <label for="name" class="form-label">Product Name</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="price" class="form-label">Price</label>
    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price ?? '') }}" required>
    @error('price')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="images" class="form-label">Product Images</label>
    <input type="file" class="form-control @error('images') is-invalid @enderror" id="images" name="images[]" multiple>
    @error('images')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if(isset($product) && $product->images->count() > 0)
<div class="mb-3">
    <label class="form-label">Current Images</label>
    <div class="row">
        @foreach($product->images as $image)
        <div class="col-md-2 mb-2">
            <img src="{{ asset('storage/' . $image->image_path) }}" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
            <button type="button" class="btn btn-sm btn-danger mt-1" onclick="deleteImage({{ $image->id }})">Delete</button>
        </div>
        @endforeach
    </div>
</div>
@endif
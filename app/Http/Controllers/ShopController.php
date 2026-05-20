<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Product;
use App\Models\Category; // Gọi Model Category
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShopController extends Controller
{
    public function index(Request $request)
    {

    {
        // Banner đang bật
        $banners = Banner::where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        // 1. Lấy toàn bộ danh mục từ bảng categories để đổ ra Cột trái
        $categories = Category::all();
        
        // ... (Các đoạn code lấy sản phẩm ở dưới bạn giữ nguyên nhé)
        $categories = Category::all();
        
        // 2. Chuẩn bị câu lệnh lấy Sản phẩm (Chỉ lấy hàng còn trong kho)
        $query = Product::with('categories')->where('stock_quantity', '>', 0);

        // 3. Xử lý TÌM KIẾM (Nếu khách gõ vào ô tìm kiếm)
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 4. Xử lý LỌC DANH MỤC (ĐÂY LÀ PHẦN CHÚNG TA VỪA THÊM)
        // Nếu trên thanh địa chỉ có chữ ?category=... thì lọc theo mã đó
        if ($request->has('category') && $request->category != '') {
            $query->where(function ($categoryQuery) use ($request) {
                $categoryQuery
                    ->where('category_id', $request->category)
                    ->orWhereHas('categories', fn ($q) => $q->where('categories.id', $request->category));
            });
        }

        // Thực thi câu lệnh và lấy dữ liệu
        $products = $query->orderBy('id', 'desc')->get();
        
// Gửi các biến products, categories và banners ra ngoài Giao diện
        return view('home', compact('products', 'categories', 'banners'));
    }
}
// Xem chi tiết 1 sản phẩm
    // 1. SỬA LẠI HÀM SHOW CŨ
    public function show($id)
    {
        // Lấy sản phẩm, lôi luôn các Đánh giá và Tên người đánh giá ra
        $product = \App\Models\Product::with(['reviews.user', 'categories'])->findOrFail($id);
        
        // Tính số sao trung bình (nếu có review thì tính, không thì mặc định 0 sao)
        $avgRating = $product->reviews->avg('rating') ?? 0;
        $relatedProducts = $this->getRelatedProducts($product);

        return view('products.detail', compact('product', 'avgRating', 'relatedProducts'));
    }

    private function getRelatedProducts(Product $product)
    {
        $keywords = $this->productKeywords($product->name);

        $productCategoryIds = $product->categories->pluck('id');

        if ($productCategoryIds->isEmpty() && $product->category_id) {
            $productCategoryIds = collect([$product->category_id]);
        }

        return Product::with(['variants', 'categories'])
            ->where('id', '!=', $product->id)
            ->where('stock_quantity', '>', 0)
            ->get()
            ->map(function ($candidate) use ($productCategoryIds, $keywords) {
                $candidateKeywords = $this->productKeywords($candidate->name);
                $matchedKeywords = count(array_intersect($keywords, $candidateKeywords));
                $candidateCategoryIds = $candidate->categories->pluck('id');

                if ($candidateCategoryIds->isEmpty() && $candidate->category_id) {
                    $candidateCategoryIds = collect([$candidate->category_id]);
                }

                $sameCategoryScore = $productCategoryIds->intersect($candidateCategoryIds)->isNotEmpty() ? 2 : 0;

                $candidate->related_score = $matchedKeywords + $sameCategoryScore;
                $candidate->matched_keywords = $matchedKeywords;

                return $candidate;
            })
            ->filter(fn ($candidate) => $candidate->matched_keywords > 0)
            ->sortByDesc('related_score')
            ->take(8)
            ->values();
    }

    private function productKeywords(string $name): array
    {
        $stopWords = ['san', 'pham', 'loai', 'cao', 'thap', 'lon', 'nho', 'bo', 'cai', 'va', 'voi', 'cho'];
        $normalizedName = Str::of($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim();

        return $normalizedName
            ->explode(' ')
            ->filter(fn ($word) => strlen($word) > 1 && ! in_array($word, $stopWords, true))
            ->unique()
            ->values()
            ->all();
    }

    // 2. THÊM HÀM MỚI ĐỂ LƯU ĐÁNH GIÁ
    public function postReview(\Illuminate\Http\Request $request, $id)
    {
        // Ràng buộc điều kiện: Phải chọn sao (1-5)
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Tạo mới đánh giá lưu vào DB
        \App\Models\Review::create([
            'product_id' => $id,
            'user_id' => auth()->id(), // Lấy ID của khách đang đăng nhập
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', '🎉 Cảm ơn bạn đã đánh giá sản phẩm!');
    }
}

import AdminLayout from "@/Layouts/AdminLayout.jsx";
import {router, usePage} from "@inertiajs/react";
import {useState, useEffect, Fragment} from "react";
import MultiSelect from "@/Components/Core/MultiSelect.jsx";
import ImageHolder from "@/Components/Core/ImageHolder.jsx";
import UploadImagePlaceHolder from "@/Components/Core/UploadImagePlaceHolder.jsx";
import ImageListItem from "@/Components/Core/ImageListItem.jsx";
import ChoseMainImageModal from "@/Components/Modals/ChoseMainImageModal.jsx";

const ProductUpdate = ({product}) => {

    const { errors } = usePage().props;
    const [selectedCategories, setSelectedCategories] = useState([]);
    const [selectedModifierGroups, setSelectedModifierGroups] = useState([]);
    const [modifierGroups, setModifierGroups] = useState([]);
    const [categories, setCategories] = useState([]);
    const [mainImage, setMainImage] = useState(null);
    const [productImages, setProductImages] = useState([]);
    const [galleryImages, setGalleryImages] = useState([]);
    const [selectedImages, setSelectedImages] = useState([]);
    const [values, setValues] = useState({
        name: product.name,
        en_name: product.en_name,
        slug: product.slug,
        en_slug: product.en_slug,
        description: product.description,
        en_description: product.en_description,
        price: product.price,
        product_image_id: product.product_image_id,
        category_ids: [],
        modifier_group_ids: [],
        product_image_ids: []
    });

    const [choseMainImageModalOpen, setChoseMainImageModalOpen] = useState(false);
    const [choseGalleryImagesModalOpen, setChoseGalleryImagesModalOpen] = useState(false);

    useEffect(() => {
        setMainImage(product.product_image);
        setGalleryImages(product.product_images);

        // Function to fetch data from the API
        const fetchCategories = async () => {
            const response = await axios.get(route('admin.categories.all'));
            setCategories(response.data.json);
            if (product.categories.length > 0) {
                setSelectedCategories(product.categories);
            }
        };

        const fetchModifierGroups = async () => {
            const response = await axios.get(route('admin.modifier-groups.all'));
            setModifierGroups(response.data.json);
            if (product.modifier_groups.length > 0) {
                setSelectedModifierGroups(product.modifier_groups);
            }
        }

        const fetchProductImages = async () => {
            const response = await axios.get(route('admin.images.all', 'product'));
            console.log(response.data.json);
            setProductImages(response.data.json);
        }

        // Call the fetchData function on page load
        fetchCategories();
        fetchModifierGroups();
        fetchProductImages();
    }, []);

    const handleSetSelectedCategories = (e) => {
        //map ids and count of occurence
        const countMap = new Map();

        e.forEach((obj) => {//populate id with occurence id counter
            const key = obj.id;
            countMap.set(key, (countMap.get(key) || 0) + 1);
        });

        const selectedCategories = e.filter((obj) => {//leave only those that occure once
            const key = (obj.id);
            return countMap.get(key) === 1;
        });

        setSelectedCategories(selectedCategories);
    }

    const handleSetSelectedModifierGroups = (e) => {
        //map ids and count of occurence
        const countMap = new Map();

        e.forEach((obj) => {//populate id with occurence id counter
            const key = obj.id;
            countMap.set(key, (countMap.get(key) || 0) + 1);
        });

        const selectedModifierGroups = e.filter((obj) => {//leave only those that occure once
            const key = (obj.id);
            return countMap.get(key) === 1;
        });

        setSelectedModifierGroups(selectedModifierGroups);
    }

    function handleChange(e) {
        setValues(values => ({
            ...values,
            [e.target.id]: e.target.value,
        }))
    }

    function handleSubmit(e) {
        values.category_ids = selectedCategories.map(item => item.id) || [];
        values.modifier_group_ids = selectedModifierGroups.map(item => item.id) || [];
        values.product_image_ids = galleryImages.map(obj => obj.id) || [];
        values.product_image_id = mainImage.id

        e.preventDefault();

        router.put(route('admin.products.update', product.id), values);
    }

    function classNames(...classes) {
        return classes.filter(Boolean).join(' ')
    }

    const addImageModalOpen = () => {
        setChoseMainImageModalOpen(true);
    }

    const addGalleryImagesOpenModal = () => {
        setChoseGalleryImagesModalOpen(true);
    }

    const handleOnImageClick = (id) => {
        setSelectedImages([id]);
        setMainImage(productImages.find(procductImage => procductImage.id === id));
        // selectedImages.includes(id) ? selectedImages(prev => prev.filter(el => el != id)) : setSelectedImages(prev => [...prev, id]); - example with multi
    }

    const handleOnGalleryImageClick = (id) => {
        const currentImage = productImages.find(productImage => productImage.id === id);
        galleryImages.some(obj => obj.id === currentImage.id) ? setGalleryImages(prev => prev.filter(el => el.id !== currentImage.id)) : setGalleryImages(prev => [...prev, currentImage]);
    }

    return <AdminLayout>

        <ChoseMainImageModal images={productImages} setOpen={setChoseMainImageModalOpen} open={choseMainImageModalOpen} selectedImages={selectedImages} handleOnImageClick={handleOnImageClick}/>

        <ChoseMainImageModal images={productImages} setOpen={setChoseGalleryImagesModalOpen} open={choseGalleryImagesModalOpen} selectedImages={galleryImages.map(obj => obj.id)} handleOnImageClick={handleOnGalleryImageClick}/>

        <div className="space-y-10 divide-y divide-gray-900/10">
            <div className="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                <div className="px-4 sm:px-0">
                    <h2 className="text-center text-base font-semibold leading-7 text-gray-900 mb-4">Chose Main Image</h2>

                    {mainImage
                        ? <ImageHolder image={mainImage} onClick={addImageModalOpen} className={'cursor-pointer'}/>
                        : <UploadImagePlaceHolder onClick={addImageModalOpen} className={'mt-16'} label={'Add Main Image.'}/>}
                    {errors.product_image_id && <div className={'text-red-500'}>{errors.product_image_id}</div>}

                    <h2 className="mt-16 text-center text-base font-semibold leading-7 text-gray-900 mb-4">Chose Gallery Images</h2>
                    {galleryImages.length > 0
                        ? <ul role="list" className="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 sm:gap-x-6 lg:grid-cols-4 xl:gap-x-8 mt-8 cursor-pointer" onClick={addGalleryImagesOpenModal}>
                            {galleryImages.map((file) => (
                                <div key={file.id}>
                                    <ImageListItem file={file} />
                                </div>
                            ))}
                        </ul> : <UploadImagePlaceHolder onClick={addGalleryImagesOpenModal} className={'mt-16'} label={'Add Gallery Images.'}/>}
                </div>

                <form onSubmit={handleSubmit} className="bg-white shadow-xs ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <div className="px-4 py-6 sm:p-8">
                        <div className="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <div className="sm:col-span-4">
                                <label htmlFor="name" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product Name
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="name"
                                            id="name"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy name"
                                            value={values.name} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.name && <div className={'text-red-500'}>{errors.name}</div>}
                                </div>
                            </div>

                            <div className="sm:col-span-4">
                                <label htmlFor="name" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product English Name
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="en_name"
                                            id="en_name"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy english name"
                                            value={values.en_name} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.en_name && <div className={'text-red-500'}>{errors.en_name}</div>}
                                </div>
                            </div>

                            <div className="sm:col-span-4">
                                <label htmlFor="website" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product Slug
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="slug"
                                            id="slug"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy slug"
                                            value={values.slug} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.slug && <div className={'text-red-500'}>{errors.slug}</div>}
                                </div>
                            </div>

                            <div className="sm:col-span-4">
                                <label htmlFor="website" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product English Slug
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="text"
                                            name="en_slug"
                                            id="en_slug"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy english slug"
                                            value={values.en_slug} onChange={handleChange}
                                        />
                                    </div>
                                    {errors.en_slug && <div className={'text-red-500'}>{errors.en_slug}</div>}
                                </div>
                            </div>

                            <div className="sm:col-span-4">
                                <label htmlFor="price" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product Price
                                </label>
                                <div className="mt-2">
                                    <div className="flex rounded-md shadow-xs ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 sm:max-w-md">
                                        <input
                                            type="number"
                                            name="price"
                                            id="price"
                                            className="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            placeholder="Some fancy price"
                                            value={values.price} onChange={handleChange}
                                        />
                                    </div>
                                </div>
                                {errors.price && <div className={'text-red-500'}>{errors.price}</div>}
                            </div>

                            <MultiSelect items={categories} selectedItems={selectedCategories} handleSetSelectedItems={handleSetSelectedCategories} label={'Categories'} placeHolder={'Select Category'} />

                            <MultiSelect items={modifierGroups} selectedItems={selectedModifierGroups} handleSetSelectedItems={handleSetSelectedModifierGroups} label={'Modifier Groups'} placeHolder={'Select Modifier Group'} />

                            <div className="col-span-full">
                                <label htmlFor="description" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product Description
                                </label>
                                <div className="mt-2">
                                      <textarea
                                          id="description"
                                          name="description"
                                          rows={3}
                                          className="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                          defaultValue={''}
                                          value={values.description}
                                          onChange={handleChange}
                                      />
                                </div>
                                {errors.description && <div className={'text-red-500'}>{errors.description}</div>}
                                <p className="mt-3 text-sm leading-6 text-gray-600">Write a few sentences about a product.</p>
                            </div>

                            <div className="col-span-full">
                                <label htmlFor="description" className="block text-sm font-medium leading-6 text-gray-900">
                                    Product English Description
                                </label>
                                <div className="mt-2">
                                      <textarea
                                          id="en_description"
                                          name="en_description"
                                          rows={3}
                                          className="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-xs ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                          defaultValue={''}
                                          value={values.en_description}
                                          onChange={handleChange}
                                      />
                                </div>
                                {errors.en_description && <div className={'text-red-500'}>{errors.en_description}</div>}
                                <p className="mt-3 text-sm leading-6 text-gray-600">Write a few sentences about a product.</p>
                            </div>

                        </div>
                    </div>
                    <div className="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
                        <a
                            href={route('admin.products.index')}
                            type="button"
                            className="text-sm font-semibold leading-6 text-gray-900">
                            Cancel
                        </a>
                        <button
                            type="submit"
                            className="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Save
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AdminLayout>
}

export default ProductUpdate;

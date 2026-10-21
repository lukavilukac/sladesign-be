const ImageHolder = ({image, onClick, className}) => {
    return <div onClick={onClick} className={`group aspect-h-7 aspect-w-10 block w-full overflow-hidden rounded-lg ${className}`}>
        <img src={image.path} alt="" className="pointer-events-none object-cover w-80 mx-auto rounded-lg" />
        <p className="pl-16 pointer-events-none mt-2 block truncate text-sm font-medium text-gray-900">{image.alt}</p>
        <p className="pl-16 pointer-events-none block text-sm font-medium text-gray-500">{image.size}</p>
    </div>
}

export default ImageHolder;
